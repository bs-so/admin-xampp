<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Event extends Model
{
    protected $table = 'olc_events';

    public function getRecordById($id) {
        $records = self::where('id', $id)
            ->get();

        if (!isset($records) || count($records) == 0) {
            return [];
        }

        return $records[0];
    }

    public function insertRecord($params) {
        $ret = self::insert([
            'title'  => $params['title'],
            'img_main'  => $params['img_main'],
            'img_slide'    => $params['img_slide'],
            'lang'    => $params['lang'],
            'status'    => $params['status'],
        ]);
        return $ret;
    }

    public function updateRecord($params) {
        $update = [
            'title'  => $params['title'],
            'lang'    => $params['lang'],
            'status'    => $params['status'],
        ];

        if (isset($params['img_main'])) {
            $update['img_main'] = $params['img_main'];
        }

        if (isset($params['img_slide'])) {
            $update['img_slide'] = $params['img_slide'];
        }

        $ret = self::where('id', $params['id'])
            ->update($update);

        return $ret;
    }

    public function deleteRecord($id) {
        $ret = self::where('id', $id)
            ->delete();

        return $ret;
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->select('*');

        // filtering
        if (isset($params['columns'][1]['search']['value'])
            && $params['columns'][1]['search']['value'] !== ''
        ) {
            $selector->where($this->table . '.title', 'like', '%' . $params['columns'][1]['search']['value'] . '%');
        }
        $selector->orderBy('id', 'DESC');

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
