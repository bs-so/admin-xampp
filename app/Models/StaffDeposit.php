<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class StaffDeposit extends Authenticatable
{
    use Notifiable;
    protected $table = 'olc_staff_deposit';
    protected $table_staff = 'olc_staff';

    public function makeCsv($filename) {
        $csv = '';
        $titles = ['no', 'staff_name', 'currency', 'wallet_addr', 'amount', 'deposit_fee', 'transfer_fee', 'status', 'gas_price', 'gas_used', 'reged_at'];
        foreach ($titles as $index => $title) {
            $csv .= trans('staff-history.deposit.' . $title) . ',';
        }
        $csv = substr($csv, 0, strlen($csv) - 1) . "\n";

        $records = DB::table($this->table)
            ->leftJoin($this->table_staff, $this->table_staff . '.id', '=', $this->table . '.staff_id')
            ->select(
                $this->table . '.*',
                $this->table_staff . '.name as staff_name'
            )
            ->get();
        foreach ($records as $index => $record) {
            $csv .= ($index + 1) . ',';
            $csv .= $record->staff_name . ',';
            $csv .= $record->currency . ',';
            $csv .= $record->wallet_addr . ',';
            $csv .= $record->amount . ',';
            $csv .= $record->deposit_fee . ',';
            $csv .= $record->transfer_fee . ',';
            $csv .= g_enum('StaffDepositStatus')[$record->status][0] . ',';
            $csv .= $record->gas_price . ',';
            $csv .= $record->gas_used . ',';
            $csv .= $record->created_at . "\n";
        }

        file_put_contents($filename, "\xEF\xBB\xBF". $csv);
    }

    public static function getAll() {
        $records = self::where('status', STATUS_ACTIVE)
            ->whereIn('role', [USER_ROLE_ADMIN])
            ->get();

        if (!isset($records) || count($records) == 0) {
            return [];
        }

        return $records;
    }

    public static function insertRecord($params) {
        $result = self::insert($params);

        return $result;
    }

    public static function getPendingRecords() {
        $records = self::where('status', STATUS_PENDING)
            ->select('*')
            ->get();

        return $records;
    }

    public static function updateTxStatus($id, $status) {
        $ret = self::where('id', $id)
            ->update([
                'status'    => $status,
            ]);

        return $ret;
    }

    public function getRecordById($id) {
        $records = DB::table($this->table)
            ->where('id', $id)
            ->select('*')
            ->get();

        if (!isset($records) || count($records) == 0) {
            return [];
        }

        return $records[0];
    }

    public function updateRecordById($id, $info) {
        $result = DB::table($this->table)
            ->where('id', $id)
            ->update($info);

        return $result;
    }

    public function updateLang($id, $lang) {
        $ret = DB::table($this->table)
            ->where('id', $id)
            ->update([
                'lang'  => $lang,
            ]);

        return $ret;
    }

    public function deleteRecordById($id) {
        $records = DB::table($this->table)
            ->where('id', $id)
            ->select('role')
            ->get();
        if (!isset($records) || count($records) == 0) {
            return -1;
        }
        if ($records[0]->role == USER_ROLE_ADMIN) {
            return 0;
        }

        $selector = DB::table($this->table)
            ->where('id', $id)
            ->delete();

        return 1;
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
            $selector->where('wallet_addr', 'like', '%' . $params['columns'][3]['search']['value'] . '%');
        }
        if (isset($params['columns'][4]['search']['value'])
            && $params['columns'][4]['search']['value'] !== ''
        ) {
           $selector->where($this->table . '.status', $params['columns'][4]['search']['value']);
        }
        if (isset($params['columns'][5]['search']['value'])
            && $params['columns'][5]['search']['value'] !== ''
        ) {
            $dateRange = preg_replace('/[\$\,]/', '', $params['columns'][5]['search']['value']);
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
