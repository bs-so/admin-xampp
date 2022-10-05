<?php

namespace App\Models;

use DB;
use Log;
use Auth;
use Litipk\BigNumbers\Decimal;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AffiliateTransfer extends Model
{
    protected $table = 'olc_affiliate_transfer';
    protected $table_staff = 'olc_staff';

    public static function insertRecord($params) {
        $ret = self::insert($params);

        return $ret;
    }

    public function makeCsv($filename) {
        $csv = '';
        $titles = ['no', 'staff', 'type', 'currency', 'amount', 'remark', 'transfered_at'];
        foreach ($titles as $index => $title) {
            $csv .= trans('affiliate.transfer.' . $title) . ',';
        }
        $csv = substr($csv, 0, strlen($csv) - 1) . "\n";

        $records = self::leftJoin($this->table_staff, $this->table_staff . '.id', '=', $this->table . '.staff_id')
            ->select(
                $this->table . '.*',
                DB::raw($this->table_staff . '.name as staff')
            )->get();
        foreach ($records as $index => $record) {
            $csv .= ($index + 1) . ',';
            $csv .= $record->staff . ',';
            $csv .= trans(g_enum('TransferTypeData')[$record->type][0]) . ',';
            $csv .= $record->currency . ',';
            $csv .= $record->amount . ',';
            $csv .= '"' . $record->remark . '",';
            $csv .= $record->created_at . "\n";
        }

        file_put_contents($filename, "\xEF\xBB\xBF". $csv);
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->leftJoin($this->table_staff, $this->table_staff . '.id', '=', $this->table . '.staff_id')
            ->select(
                $this->table . '.*',
                $this->table_staff . '.name as staff'
            );

        // filtering
        if (isset($params['columns'][1]['search']['value'])
            && $params['columns'][1]['search']['value'] !== ''
        ) {
            $selector->where($this->table_staff . '.name', 'like', '%' . $params['columns'][1]['search']['value'] . '%');
        }
        if (isset($params['columns'][2]['search']['value'])
            && $params['columns'][2]['search']['value'] !== ''
        ) {
            $selector->where($this->table . '.type', $params['columns'][2]['search']['value']);
        }
        if (isset($params['columns'][3]['search']['value'])
            && $params['columns'][3]['search']['value'] !== ''
        ) {
            $selector->where('currency', $params['columns'][3]['search']['value']);
        }
        if (isset($params['columns'][6]['search']['value'])
            && $params['columns'][6]['search']['value'] !== ''
        ) {
            $amountRange = preg_replace('/[\$\,]/', '', $params['columns'][6]['search']['value']);
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
            'recordsTotal' => DB::table($this->table)->count(),
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }
}
