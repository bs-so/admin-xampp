<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class TraderDeposit extends Authenticatable
{
    use Notifiable;
    protected $table = 'olc_users_deposit';
    protected $table_user = 'olc_users';

    public function makeCsv($filename, $trader_id = 0) {
        $csv = '';
        $titles = ['no', 'user_name', 'currency', 'wallet_addr', 'amount', 'deposit_fee', 'transfer_fee', 'status', 'gas_price', 'gas_used', 'tx_id', 'reged_at'];
        foreach ($titles as $index => $title) {
            $csv .= trans('users-history.deposit.' . $title) . ',';
        }
        $csv = substr($csv, 0, strlen($csv) - 1) . "\n";

        $selector = DB::table($this->table)
            ->leftJoin($this->table_user, $this->table_user . '.id', '=', $this->table . '.user_id')
            ->select(
                $this->table . '.*',
                $this->table_user . '.nickname as user_name'
            );
        if ($trader_id > 0) {
            $selector->where('user_id', $trader_id);
        }
        $records = $selector->get();
        foreach ($records as $index => $record) {
            $csv .= ($index + 1) . ',';
            $csv .= $record->user_name . ',';
            $csv .= $record->currency . ',';
            $csv .= $record->wallet_addr . ',';
            $csv .= $record->amount . ',';
            $csv .= $record->deposit_fee . ',';
            $csv .= $record->transfer_fee . ',';
            $csv .= g_enum('UsersDepositStatus')[$record->status][0] . ',';
            $csv .= $record->gas_price . ',';
            $csv .= $record->gas_used . ',';
            $csv .= $record->tx_id . ',';
            $csv .= $record->created_at . "\n";
        }

        file_put_contents($filename, "\xEF\xBB\xBF". $csv);
    }

    public function insertRecord($params) {
        $ret = self::insert($params);

        return $ret;
    }

    public static function getPendingRecords() {
        $records = self::where('status', STATUS_PENDING)
            ->select('*')
            ->get();

        return $records;
    }

    public static function getUserTotalDeposit($user_id, $currency) {
        $record = self::where('user_id', $user_id)
            ->where('currency', $currency)
            ->select(DB::raw('sum(amount) as total'))
            ->first();

        if (!isset($record) || !isset($record->total)) {
            return 0;
        }

        return $record->total;
    }

    public static function updateTxStatus($id, $status) {
        $ret = self::where('id', $id)
            ->update([
                'status'    => $status,
            ]);

        return $ret;
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->leftJoin($this->table_user, $this->table_user . '.id', '=', $this->table . '.user_id')
            ->select(
                $this->table . '.*',
                $this->table_user . '.userid as userid',
                $this->table_user . '.nickname as user_name'
            );

        if (isset($params['user_id'])) {
            $selector->where('user_id', $params['user_id']);
        }
        $recordsTotal = $selector->count();

        // filtering
        if (isset($params['columns'][1]['search']['value'])
            && $params['columns'][1]['search']['value'] !== ''
        ) {
            $selector->where($this->table_user . '.userid', 'like', '%' . $params['columns'][1]['search']['value'] . '%');
        }
        if (isset($params['columns'][2]['search']['value'])
            && $params['columns'][2]['search']['value'] !== ''
        ) {
            $selector->where($this->table_user . '.nickname', 'like', '%' . $params['columns'][2]['search']['value'] . '%');
        }
        if (isset($params['columns'][3]['search']['value'])
            && $params['columns'][3]['search']['value'] !== ''
        ) {
            $selector->where('currency', 'like', '%' . $params['columns'][3]['search']['value'] . '%');
        }
        if (isset($params['columns'][4]['search']['value'])
            && $params['columns'][4]['search']['value'] !== ''
        ) {
            $selector->where('wallet_addr', 'like', '%' . $params['columns'][4]['search']['value'] . '%');
        }
        if (isset($params['columns'][5]['search']['value'])
            && $params['columns'][5]['search']['value'] !== ''
        ) {
            $selector->where('amount', $params['columns'][5]['search']['value']);
        }
        if (isset($params['columns'][6]['search']['value'])
            && $params['columns'][6]['search']['value'] !== ''
        ) {
            $selector->where('tx_id', 'like', '%' . $params['columns'][6]['search']['value'] . '%');
        }
        if (isset($params['columns'][7]['search']['value'])
            && $params['columns'][7]['search']['value'] !== ''
        ) {
           $selector->where($this->table . '.status', $params['columns'][7]['search']['value']);
        }
        if (isset($params['columns'][8]['search']['value'])
            && $params['columns'][8]['search']['value'] !== ''
        ) {
            $amountRange = preg_replace('/[\$\,]/', '', $params['columns'][8]['search']['value']);
            $elements = explode(':', $amountRange);

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
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }
}
