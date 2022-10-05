<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Inquiry extends Model
{
    protected $table = 'olc_inquiries';
    protected $table_user = 'olc_users';

    public function getRecordById($id) {
        $records = self::where('id', $id)
            ->get();

        if (!isset($records) || count($records) == 0) {
            return [];
        }

        return $records[0];
    }

    public function updateRecord($params) {
        $ret = self::where('id', $params['id'])
            ->update([
                'status'    => $params['status'],
            ]);

        return $ret;
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->leftJoin($this->table_user, $this->table_user . '.email', '=', $this->table . '.email')
            ->select(
                $this->table . '.*',
                $this->table_user . '.id as user_id'
            );

        // filtering
        if (isset($params['columns'][1]['search']['value'])
            && $params['columns'][1]['search']['value'] !== ''
        ) {
            $selector->where($this->table . '.title', 'like', '%' . $params['columns'][1]['search']['value'] . '%');
        }
        if (isset($params['columns'][2]['search']['value'])
            && $params['columns'][2]['search']['value'] !== ''
        ) {
            $selector->where($this->table . '.content', 'like', '%' . $params['columns'][2]['search']['value'] . '%');
        }
        if (isset($params['columns'][3]['search']['value'])
            && $params['columns'][3]['search']['value'] !== ''
        ) {
            $selector->where($this->table . '.email', 'like', '%' . $params['columns'][3]['search']['value'] . '%');
        }
        if (isset($params['columns'][4]['search']['value'])
            && $params['columns'][4]['search']['value'] !== ''
        ) {
           $selector->where($this->table . '.status', $params['columns'][4]['search']['value']);
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
