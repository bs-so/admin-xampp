<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class GasUsage extends Model
{
    use Notifiable;
    protected $table = 'olc_gas_usage';

    public function makeCsv($filename, $trader_id = 0) {
        $csv = '';
        $titles = ['no', 'currency', 'tx_id', 'to_address', 'gas_sent', 'gas_used', 'remark', 'used_at'];
        foreach ($titles as $index => $title) {
            $csv .= trans('statistics.gas_usage.' . $title) . ',';
        }
        $csv = substr($csv, 0, strlen($csv) - 1) . "\n";

        $records = DB::table($this->table)
            ->select('*')
            ->get();
        foreach ($records as $index => $record) {
            $csv .= ($index + 1) . ',';
            $csv .= $record->currency . ',';
            $csv .= $record->tx_id . ',';
            $csv .= $record->to_address . ',';
            $csv .= $record->gas_sent . ',';
            $csv .= $record->gas_used . ',';
            $csv .= $record->remark . ',';
            $csv .= $record->created_at . "\n";
        }

        file_put_contents($filename, "\xEF\xBB\xBF". $csv);
    }

    public function insertRecord($params) {
        $ret = DB::table($this->table)
            ->insert($params);

        return $ret;
    }

    public function getTotalUsed() {
        $ret = self::select(DB::raw('sum(gas_used) as total'))
            ->get();

        if (!isset($ret) || count($ret) == 0) {
            return 0;
        }

        return $ret[0]->total;
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
            $selector->where('currency', 'like', '%' . $params['columns'][1]['search']['value'] . '%');
        }
        if (isset($params['columns'][2]['search']['value'])
            && $params['columns'][2]['search']['value'] !== ''
        ) {
            $selector->where('tx_id', 'like', '%' . $params['columns'][2]['search']['value'] . '%');
        }
        if (isset($params['columns'][3]['search']['value'])
            && $params['columns'][3]['search']['value'] !== ''
        ) {
            $selector->where('to_address', 'like', '%' . $params['columns'][3]['search']['value'] . '%');
        }
        if (isset($params['columns'][6]['search']['value'])
            && $params['columns'][6]['search']['value'] !== ''
        ) {
            $selector->where('remark', 'like', '%' . $params['columns'][6]['search']['value'] . '%');
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
