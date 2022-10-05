<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Banks extends Model
{
    use Notifiable;
    protected $table = 'olc_banks';

    public function getAll() {
        $records = self::where('status', STATUS_ACTIVE)
            ->pluck('name', 'id');

        return $records;
    }

    public function getRecordById($id) {
        $record = self::where('id', $id)
            ->select('*')
            ->first();

        return $record;
    }

    public function insertRecord($params) {
        $ret = self::insert([
            'name'      => $params['name'],
            'status'    => $params['status'],
        ]);

        return $ret;
    }

    public function updateRecord($params) {
        $ret = self::where('id', $params['id'])
            ->update([
                'name'      => $params['name'],
                'status'    => $params['status'],
            ]);

        return $ret;
    }

    public function deleteRecordById($id) {
        $ret = self::where('id', $id)
            ->delete();

        return $ret;
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->select(
                '*'
            );
        $recordsTotal = $selector->count();

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
