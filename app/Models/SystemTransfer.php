<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class SystemTransfer extends Model
{
    protected $table = 'olc_system_transfer';
    protected $table_users = 'olc_users';
    protected $table_staff = 'olc_staff';

    public static function insertRecord($params) {
        $ret = self::insert($params);

        return $ret;
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->leftJoin($this->table_staff, $this->table_staff . '.id', '=', $this->table . '.staff_id')
            ->leftJoin($this->table_users, $this->table_users . '.id', '=', $this->table . '.user_id')
            ->select(
                $this->table . '.*',
                $this->table_staff . '.name as operator',
                $this->table_users . '.userid'
            );

        if (isset($params['types'])) {
            $selector->whereIn('type', $params['types']);
        }
        $recordsTotal = $selector->count();

        // filtering
        if (isset($params['columns'][2]['search']['value'])
            && $params['columns'][2]['search']['value'] !== ''
        ) {
            $selector->where($this->table . '.type', $params['columns'][2]['search']['value']);
        }
        if (isset($params['columns'][3]['search']['value'])
            && $params['columns'][3]['search']['value'] !== ''
        ) {
            $selector->where($this->table . '.direction', $params['columns'][3]['search']['value']);
        }
        if (isset($params['columns'][4]['search']['value'])
            && $params['columns'][4]['search']['value'] !== ''
        ) {
            $selector->where($this->table_users . '.userid', 'like', '%' . $params['columns'][4]['search']['value'] . '%');
        }
        if (isset($params['columns'][5]['search']['value'])
            && $params['columns'][5]['search']['value'] !== ''
        ) {
            $selector->where('currency', $params['columns'][5]['search']['value']);
        }
        if (isset($params['columns'][8]['search']['value'])
            && $params['columns'][8]['search']['value'] !== ''
        ) {
           $selector->where($this->table . '.status', $params['columns'][8]['search']['value']);
        }
        if (isset($params['columns'][9]['search']['value'])
            && $params['columns'][9]['search']['value'] !== ''
        ) {
            $amountRange = preg_replace('/[\$\,]/', '', $params['columns'][9]['search']['value']);
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
