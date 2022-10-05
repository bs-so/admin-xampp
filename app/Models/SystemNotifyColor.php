<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class SystemNotifyColor extends Model
{
    protected $table = 'olc_notifications_color';

    public function getAll() {
        $records = self::select('*')->get();
        return $records;
    }

    public function updateRecord($params) {
        for ($i = 1 ; $i < 4; $i ++) {
            if (!empty($params["color$i"])) {
                $ret = self::where('id', $i)
                    ->update([
                        'color'       => $params["color$i"],
                    ]);
            }
        }
        
        return 1;
    }
}
