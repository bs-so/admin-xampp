<?php

namespace App\Models;

use DB;
use App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class GameCategoryNames extends Model
{
    protected $table = 'olc_game_category_names';

    public function getCategoryName($category_id, $lang) {
        $record = self::where('category_id', $category_id)
            ->where('lang', $lang)
            ->select('name')
            ->first();

        if (!isset($record) || !isset($record->name)) {
            return '';
        }

        return $record->name;
    }

    public function getCategoryNames($category_ids, $lang) {
        $records = self::whereIn('category_id', $category_ids)
            ->where('lang', $lang)
            ->select('name')
            ->get();

        if (!isset($records) || count($records) == 0) {
            return '';
        }

        $result = array();
        foreach ($records as $index => $record) {
            $result[] = $record->name;
        }

        return $result;
    }
}
