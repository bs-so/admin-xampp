<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class MailModel extends Model
{
    protected $table = 'olc_mail_announce';
    protected $table_queue = 'olc_mail_announce_detail';

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

    public function insertRecord($params) {
        $ret = self::insertGetId([
            'title' => $params['title'],
            'content'   => $params['content'],
            'type'      => $params['type'],
            'total'     => $params['total'],
            'success'   => 0,
        ]);

        return $ret;
    }

    public function updateSuccess($id) {
        DB::table($this->table)
            ->where('id', $id)
            ->update([
                'success'   => DB::raw('success+1'),
            ]);
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table);

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
