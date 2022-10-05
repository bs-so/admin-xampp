<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Closing extends Model
{
    use Notifiable;
    protected $table = 'olc_closing';

    public function getNowSetting() {
        $record = self::select('*')->first();

        return $record;
    }

    public function updateApplyStatus($id, $status) {
        $ret = self::where('id', $id)
            ->update([
                'apply_status'  => $status,
            ]);

        return $ret;
    }

    public function updateStatus($id, $status) {
        $ret = self::where('id', $id)
            ->update([
                'status'        => $status,
            ]);

        return $ret;
    }

    public function updateSetting($params) {
        $record = self::select('id')->first();

        if (!isset($record) || !isset($record->id)) {
            $ret = self::insertGetId([
                'start_at'      => $params['start_at_date'] . ' ' . $params['start_at_time'],
                'finish_at'     => $params['finish_at_date'] . ' ' . $params['finish_at_time'],
                'status'        => !isset($params['status']) ? STATUS_BANNED : STATUS_ACTIVE,
                'apply_status'  => CLOSING_STATUS_NOT_APPLIED,
            ]);
        }
        else {
            $ret = self::where('id', $record->id)
                ->update([
                    'start_at'      => $params['start_at_date'] . ' ' . $params['start_at_time'],
                    'finish_at'     => $params['finish_at_date'] . ' ' . $params['finish_at_time'],
                    'status'        => !isset($params['status']) ? STATUS_BANNED : STATUS_ACTIVE,
                    'apply_status'  => CLOSING_STATUS_NOT_APPLIED,
                ]);
        }

        return $ret;
    }
}
