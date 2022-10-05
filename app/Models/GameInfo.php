<?php

namespace App\Models;

use DB;
use Log;
use App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class GameInfo extends Model
{
    protected $table = 'olc_game_info';

    public function getRecordById($id) {
        $record = self::where('id', $id)
            ->select('*')
            ->first();

        return $record;
    }

    public function insertRecord1($params) {
        $newId = self::insertGetId([
            'name'          => $params['name'],
            'category'      => $params['category'],
            'main_img'      => '',
            'mobile_img_jp' => '',
            'mobile_img_en' => '',
            'desc_img1_jp'  => '',
            'desc_img2_jp'  => '',
            'desc_img1_en'  => '',
            'desc_img2_en'  => '',
            'video_img'     => '',
        ]);

        return $newId;
    }

    public function insertRecord2($newId, $params) {
        $ret = self::where('id', $newId)
            ->update([
                'main_img'      => $params['main_img_url'],
                'mobile_img_jp' => $params['mobile_img_jp_url'],
                'mobile_img_en' => $params['mobile_img_en_url'],
                'desc_img1_jp'  => $params['desc_img1_jp_url'],
                'desc_img2_jp'  => $params['desc_img2_jp_url'],
                'desc_img1_en'  => $params['desc_img1_en_url'],
                'desc_img2_en'  => $params['desc_img2_en_url'],
                'video_img'     => $params['video_img_url'],
                'video'         => $params['video_url'],
            ]);

        return $ret;
    }

    public function updateRecord($params) {
        try {
            DB::beginTransaction();

            $ret = self::where('id', $params['id'])
                ->update([
                    'name'      => $params['name'],
                    'category'  => $params['category'],
                ]);

            if (isset($params['main_img_url'])) {
                $ret = self::where('id', $params['id'])
                    ->update([
                        'main_img'      => $params['main_img_url'],
                    ]);
            }
            if (isset($params['mobile_img_jp_url'])) {
                $ret = self::where('id', $params['id'])
                    ->update([
                        'mobile_img_jp' => $params['mobile_img_jp_url'],
                    ]);
            }
            if (isset($params['mobile_img_en_url'])) {
                $ret = self::where('id', $params['id'])
                    ->update([
                        'mobile_img_en' => $params['mobile_img_en_url'],
                    ]);
            }
            if (isset($params['desc_img1_jp_url'])) {
                $ret = self::where('id', $params['id'])
                    ->update([
                        'desc_img1_jp'  => $params['desc_img1_jp_url'],
                    ]);
            }
            if (isset($params['desc_img2_jp_url'])) {
                $ret = self::where('id', $params['id'])
                    ->update([
                        'desc_img2_jp'  => $params['desc_img2_jp_url'],
                    ]);
            }
            if (isset($params['desc_img1_en_url'])) {
                $ret = self::where('id', $params['id'])
                    ->update([
                        'desc_img1_en'  => $params['desc_img1_en_url'],
                    ]);
            }
            if (isset($params['desc_img2_en_url'])) {
                $ret = self::where('id', $params['id'])
                    ->update([
                        'desc_img2_en'  => $params['desc_img2_en_url'],
                    ]);
            }
            if (isset($params['video_img_url'])) {
                $ret = self::where('id', $params['id'])
                    ->update([
                        'video_img'     => $params['video_img_url'],
                    ]);
            }
            if (isset($params['video_url'])) {
                $ret = self::where('id', $params['id'])
                    ->update([
                        'video'         => $params['video_url'],
                    ]);
            }

            DB::commit();
            return $ret;
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

        return $ret;
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->select('*');

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
            $category_ids = json_decode($record->category);
            $record->category_name = $tbl->getCategoryNames($category_ids, $locale);
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
