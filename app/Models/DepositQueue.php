<?php

namespace App\Models;

use DB;
use Log;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class DepositQueue extends Model
{
    use Notifiable;
    protected $table = 'olc_users_deposit_queue';

    public function getAll() {
        $records = DB::table($this->table)
            ->orderBy('requested', 'desc')
            //->where('status', DEPOSIT_QUEUE_STATUS_INIT)
            ->select('user_id')
            ->get();

        return $records;
    }

    public function pickTop()  {
        $records = DB::table($this->table)
            ->orderBy('requested', 'desc')
            ->take(1)
            ->select('user_id')
            ->get();

        if (!isset($records) || count($records) == 0) {
            return [];
        }

        return $records;
    }

    public function deleteRecords($user_id) {
        try {
            DB::beginTransaction();

            $ret = DB::table($this->table)
                ->where('user_id', $user_id)
                ->delete();

            DB::commit();

            return 1;
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::channel('crypto')->error($e->getMessage());
            return 0;
        }
    }
}
