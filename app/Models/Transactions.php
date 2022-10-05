<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Transactions extends Model
{
    use Notifiable;
    protected $table = 'olc_transactions';

    public function makeCsv($filename, $trader_id = 0) {
        $csv = '';
        $titles = ['no', 'currency', 'from_address', 'to_address', 'amount', 'tx_id', 'status', 'created_at'];
        foreach ($titles as $index => $title) {
            $csv .= trans('transactions.table.' . $title) . ',';
        }
        $csv = substr($csv, 0, strlen($csv) - 1) . "\n";

        $records = DB::table($this->table)
            ->select('*')
            ->get();
        foreach ($records as $index => $record) {
            $csv .= ($index + 1) . ',';
            $csv .= $record->currency . ',';
            $csv .= $record->from_address . ',';
            $csv .= $record->to_address . ',';
            $csv .= $record->amount . ',';
            $csv .= $record->tx_id . ',';
            $csv .= g_enum('TransferStatusData')[$record->status][0] . ',';
            $csv .= $record->created_at . "\n";
        }

        file_put_contents($filename, "\xEF\xBB\xBF". $csv);
    }

    public function insertRecord($params) {
        $ret = DB::table($this->table)
            ->insert([
                'currency'      => $params['currency'],
                'from_address'  => $params['from_address'],
                'to_address'    => $params['to_address'],
                'from_wallet'   => $params['from_wallet'],
                'to_wallet'     => $params['to_wallet'],
                'nonce'         => $params['nonce'],
                'amount'        => $params['amount'],
                'tx_id'         => '',
                'signed_hash'   => $params['signed'],
                'transfer_fee'  => 0,
                'gas_price'     => (isset($params['gas_price']) ? $params['gas_price'] : 0),
                'gas_limit'     => (isset($params['gas_limit']) ? $params['gas_limit'] : 0),
                'status'        => TRANSFER_STATUS_PENDING,
                'remark'        => $params['remark'],
            ]);

        return $ret;
    }

    public static function updateRecord($where, $data) {
        $ret = self::where($where)
            ->update($data);

        return $ret;
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->select(
                '*'
            );

        // filtering
        if (isset($params['columns'][1]['search']['value'])
            && $params['columns'][1]['search']['value'] !== ''
        ) {
            $selector->where('currency', $params['columns'][1]['search']['value']);
        }
        if (isset($params['columns'][2]['search']['value'])
            && $params['columns'][2]['search']['value'] !== ''
        ) {
            $selector->where(DB::raw('CONCAT(from_address, to_address)'), 'like', '%' . $params['columns'][2]['search']['value'] . '%');
        }
        if (isset($params['columns'][5]['search']['value'])
            && $params['columns'][5]['search']['value'] !== ''
        ) {
            $selector->where('tx_id', 'like', '%' . $params['columns'][5]['search']['value'] . '%');
        }
        if (isset($params['columns'][6]['search']['value'])
            && $params['columns'][6]['search']['value'] !== ''
        ) {
            $selector->where('status', $params['columns'][6]['search']['value']);
        }
        if (isset($params['columns'][7]['search']['value'])
            && $params['columns'][7]['search']['value'] !== ''
        ) {
            $amountRange = preg_replace('/[\$\,]/', '', $params['columns'][7]['search']['value']);
            $elements = explode(':', $amountRange);

            if ($elements[0] != "" || $elements[1] != "") {
                $elements[0] .= ' 00:00:00';
                $elements[1] .= ' 23:59:59';
                $selector->whereBetween('created_at', $elements);
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
