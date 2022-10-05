<?php

namespace App\Models;

use DB;
use Log;
use App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class GameCategory extends Model
{
    protected $table = 'olc_game_category';
    protected $table_names = 'olc_game_category_names';

    public function getAll() {
        $locale = App::getLocale();

        $records = self::leftJoin($this->table_names, $this->table_names . '.category_id', '=', $this->table . '.id')
            ->where($this->table_names . '.lang', $locale)
            ->pluck($this->table_names . '.name', $this->table . '.id');

        return $records;
    }

    public function getRecordById($id) {
        $record = self::where('id', $id)
            ->select('*')
            ->first();

        $record->names = DB::table($this->table_names)
            ->where('category_id', $id)
            ->pluck('name', 'lang');

        return $record;
    }

    public function insertRecord($params) {
        try {
            DB::beginTransaction();

            $newId = self::insertGetId([
                'status'    => $params['status'],
            ]);

            foreach (g_enum('Languages') as $lang => $data) {
                $ret = DB::table($this->table_names)
                    ->insert([
                        'category_id'   => $newId,
                        'lang'          => $lang,
                        'name'          => $params[$lang],
                    ]);
            }

            DB::commit();
            return true;
        }
        catch (\Exception $ex) {
            DB::rollback();
            Log::error($ex->getMessage());
            return false;
        }
    }

    public function updateRecord($params) {
        try {
            DB::beginTransaction();

            $ret = self::where('id', $params['id'])
                ->update([
                    'status'    => $params['status'],
                ]);

            foreach (g_enum('Languages') as $lang => $data) {
                $ret = DB::table($this->table_names)
                    ->where('category_id', $params['id'])
                    ->where('lang', $lang)
                    ->update([
                        'name'          => $params[$lang],
                    ]);
            }

            DB::commit();
            return true;
        }
        catch (\Exception $ex) {
            DB::rollback();
            Log::error($ex->getMessage());
            return false;
        }
    }

    public function deleteRecord($id) {
        $ret = self::where('id', $id)
            ->delete();
        $ret = DB::table($this->table_names)
            ->where('category_id', $id)
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

        $locale = App::getLocale();
        $tbl = new GameCategoryNames();
        foreach ($records as $index => $record) {
            $record->name = $tbl->getCategoryName($record->id, $locale);
        }

        return [
            'draw' => $params['draw']+0,
            'recordsTotal' => DB::table($this->table)->count(),
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }
}
