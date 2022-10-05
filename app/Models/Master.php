<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Master extends Model
{
    use Notifiable;
    protected $table = 'olc_master';

    public function getAll() {
        $records = self::select('*')
            ->get();

        return $records;
    }

    public static function setMaintenance($status) {
        $ret = self::where('option', MAINTENANCE_MODE)
            ->update([
                'value'     => $status,
            ]);

        return $ret;
    }

    public static function getValue($option) {
        $record = self::where('option', $option)
            ->select('value')
            ->first();

        if (!isset($record) || !isset($record->value)) {
            return 0;
        }

        return $record->value;
    }

    public function updateAll($params) {
        $ret = true;
        foreach ($params as $option => $value) {
            if (!isset($value)) continue;
            $ret = self::where('option', $option)
                ->update([
                    'value' => $value,
                ]);
        }

        return $ret;
    }
}
