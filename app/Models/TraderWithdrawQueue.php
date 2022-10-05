<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TraderWithdrawQueue extends Model
{
    use Notifiable;
    protected $table = 'olc_users_withdraw_queue';

    public static function updateStateByWhere($where, $data) {
        $ret = self::where($where)->first();

        try {
            if (isset($data['tx_id']) && $data['tx_id'] != '') {
                $ret->tx_id = $data['tx_id'];
            }
            if (isset($data['status'])) {
                $ret->status = $data['status'];
            }
            $ret->save();
        } catch(\Exception $e) {
            return false;
        }

        return true;
    }

    public static function updateState($id, $tx_id, $status) {
        $ret = self::where('id', $id)
            ->update([
                'tx_id'     => $tx_id,
                'status'    => $status,
            ]);

        return $ret;
    }
}
