<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class FAQCategory extends Model
{
    protected $table = 'olc_faq_categories';

    public function getAll($lang = 'jp') {
        $records = self::where('lang', $lang)
			->pluck('name', 'id');

        return $records;
    }

    public function getRecordById($id) {
        $records = self::where('id', $id)
            ->get();

        if (!isset($records) || count($records) == 0) {
            return [];
        }

        return $records[0];
    }

	public function insertRecord($params) {
        $ret = self::insert($params);

        return $ret;
    }

    public function updateRecord($params) {
        $ret = self::where('id', $params['id'])
            ->update([
				'lang'		=> $params['lang'],
				'name'		=> $params['name'],
            ]);

        return $ret;
    }

    public function deleteRecord($id) {
        $ret = self::where('id', $id)
            ->delete();

        return $ret;
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->select(
                $this->table . '.*'
            );

        // filtering
        if (isset($params['columns'][1]['search']['value'])
            && $params['columns'][1]['search']['value'] !== ''
        ) {
            $selector->where($this->table . '.question', 'like', '%' . $params['columns'][1]['search']['value'] . '%');
        }
        if (isset($params['columns'][3]['search']['value'])
            && $params['columns'][3]['search']['value'] !== ''
        ) {
            $selector->where($this->table . '.category', $params['columns'][3]['search']['value']);
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
