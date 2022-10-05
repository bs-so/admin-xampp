<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ServerInfo extends Model
{
    use Notifiable;
    protected $table = 'olc_server_info';

    public function getInfo($item) {
        $record = self::where('item', $item)
            ->select('value')
            ->first();

        if (!isset($record) || !isset($record->value)) {
            return '';
        }

        return $record->value;
    }
}
