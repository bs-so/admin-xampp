<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class TraderExchange extends Authenticatable
{
    use Notifiable;
    protected $table = 'olc_users_exchange';
    protected $table_user = 'olc_users';

    public function makeCsv($filename, $trader_id = 0) {
        $csv = '';
        $titles = ['no', 'user_name', 'currency_from', 'currency_to', 'amount_total', 'amount_exch', 'amount_to', 'ex_rate', 'exchange_fee', 'status', 'reged_at'];
        foreach ($titles as $index => $title) {
            $csv .= trans('users-history.exchange.' . $title) . ',';
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
            $csv .= $record->currency_from . ',';
            $csv .= $record->currency_to . ',';
            $csv .= $record->amount_total . ',';
            $csv .= $record->amount_exch . ',';
            $csv .= $record->amount_to . ',';
            $csv .= $record->ex_rate . ',';
            $csv .= $record->exchange_fee . ',';
            $csv .= g_enum('UsersExchangeStatus')[$record->status][0] . ',';
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

    public function createRecord($record) {
        $result = DB::table($this->table)
            ->insert($record);

        return $result;
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
            $selector->where('currency_from', 'like', '%' . $params['columns'][3]['search']['value'] . '%');
        }
        if (isset($params['columns'][4]['search']['value'])
            && $params['columns'][4]['search']['value'] !== ''
        ) {
            $selector->where('currency_to', 'like', '%' . $params['columns'][4]['search']['value'] . '%');
        }
        if (isset($params['columns'][9]['search']['value'])
            && $params['columns'][9]['search']['value'] !== ''
        ) {
           $selector->where($this->table . '.status', $params['columns'][9]['search']['value']);
        }
        if (isset($params['columns'][10]['search']['value'])
            && $params['columns'][10]['search']['value'] !== ''
        ) {
            $amountRange = preg_replace('/[\$\,]/', '', $params['columns'][10]['search']['value']);
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
