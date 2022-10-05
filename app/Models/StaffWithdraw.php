<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class StaffWithdraw extends Authenticatable
{
    use Notifiable;
    protected $table = 'olc_staff_withdraw';
    protected $table_staff = 'olc_staff';
    protected $table_wallets = 'olc_cold_wallets';

    public function insertRecord($params) {
        $newId = self::insertGetId([
            'staff_id'          => $params['staff_id'],
            'currency'          => $params['currency'],
            'destination'       => $params['address'],
            'amount'            => $params['amount'],
            'withdraw_fee'      => 0,
            'transfer_fee'      => 0,
            'gas_used'          => 0,
            'tx_id'             => '',
            'status'            => STATUS_REQUESTED,
            'remark'            => $params['remark']
        ]);

        return $newId;
    }

    public function updateRecord($params) {
        $ret = self::where('id', $params['id'])
            ->update([
                'withdraw_fee'      => $params['withdraw_fee'],
                'transfer_fee'      => $params['transfer_fee'],
                'gas_used'          => $params['gas_used'],
                'tx_id'             => $params['tx_id'],
                'status'            => $params['status'],
            ]);

        return $ret;
    }

    public function getWalletBalances($currency) {
        $records = DB::table($this->table_wallets)
            ->where('currency', $currency)
            ->where('type', WALLET_TYPE_WITHDRAW)
            ->where('specified', WALLET_SPECIFIED_WITHDRAW)
            ->select('*')
            ->get();

        if (!isset($records) || count($records) == 0) {
            return 1;
        }

        $result = array(
            $currency   => 0,
            'ETH'       => 0,
        );

        $tbl = new ColdWallets();
        foreach ($records as $index => $record) {
            $wallet_address = $record->wallet_address;
            $ret = $tbl->getBalance([
                'currency'          => $currency,
                'wallet_address'    => $wallet_address,
            ]);

            if (!isset($ret['balance'])) {
                return 2;
            }

            $result[$currency] = $ret['balance'];
        }

        // If token, then get gastank balance
        if ($currency == 'ETH' || $currency == 'USDT') {
            $records = DB::table($this->table_wallets)
                ->where('currency', 'ETH')
                ->where('type', WALLET_TYPE_GASTANK)
                ->where('specified', WALLET_SPECIFIED_GASTANK)
                ->select('*')
                ->get();

            if (!isset($records) || count($records) == 0) {
                return 3;
            }

            foreach ($records as $index => $record) {
                $wallet_address = $record->wallet_address;
                $ret = $tbl->getBalance([
                    'currency' => 'ETH',
                    'wallet_address' => $wallet_address,
                ]);

                if (!isset($ret['balance'])) {
                    return 4;
                }

                $result['GAS'] = $ret['balance'];
            }
        }

        return $result;
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->leftJoin($this->table_staff, $this->table_staff . '.id', '=', $this->table . '.staff_id')
            ->select(
                $this->table . '.*',
                $this->table_staff . '.name as staff_name'
            );

        if (isset($params['staff_id'])) {
            $selector->where('staff_id', $params['staff_id']);
        }

        // filtering
        if (isset($params['columns'][1]['search']['value'])
            && $params['columns'][1]['search']['value'] !== ''
        ) {
			$selector->where($this->table_staff . '.name', 'like', '%' . $params['columns'][1]['search']['value'] . '%');
        }
        if (isset($params['columns'][2]['search']['value'])
            && $params['columns'][2]['search']['value'] !== ''
        ) {
            $selector->where('currency', 'like', '%' . $params['columns'][2]['search']['value'] . '%');
        }
        if (isset($params['columns'][3]['search']['value'])
            && $params['columns'][3]['search']['value'] !== ''
        ) {
            $selector->where('type', $params['columns'][3]['search']['value']);
        }
        if (isset($params['columns'][4]['search']['value'])
            && $params['columns'][4]['search']['value'] !== ''
        ) {
            $selector->where('destination', 'like', '%' . $params['columns'][4]['search']['value'] . '%');
        }
        if (isset($params['columns'][5]['search']['value'])
            && $params['columns'][5]['search']['value'] !== ''
        ) {
           $selector->where('tx_id', 'like', '%' . $params['columns'][5]['search']['value'] . '%');
        }
        if (isset($params['columns'][6]['search']['value'])
            && $params['columns'][6]['search']['value'] !== ''
        ) {
           $selector->where($this->table . '.status', $params['columns'][6]['search']['value']);
        }

        if (isset($params['columns'][7]['search']['value'])
            && $params['columns'][7]['search']['value'] !== ''
        ) {
            $dateRange = preg_replace('/[\$\,]/', '', $params['columns'][7]['search']['value']);
            $elements = explode(':', $dateRange);

            if ($elements[0] != "" || $elements[1] != "") {
                $elements[0] .= ' 00:00:00';
                $elements[1] .= ' 23:59:59';
                $selector->whereBetween($this->table . '.created_at', $elements);
            }
        }

        // number of filtered records
        $recordsFiltered = $selector->count();

        // sort
        foreach ($params['order'] as $order) {
            $field = $params['columns'][$order['column']]['data'];
            $selector->orderBy($field, $order['dir']);
        }

        // offset & limit
        if (!empty($params['start']) && $params['start'] > 0) {
            $selector->skip($params['start']);
        }

        if (!empty($params['length']) && $params['length'] > 0) {
            $selector->take($params['length']);
        }

        // get records
        $records = $selector->get();

        return [
            'draw' => $params['draw']+0,
            'recordsTotal' => DB::table($this->table)->count(),
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }
}
