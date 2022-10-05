<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class MailQueue extends Model
{
    protected $table = 'olc_mail_announce_detail';
    protected $table_queue = 'olc_mail_announce';
    protected $user_table = 'olc_users';

    public function insertRecord($params) {
        $ret = self::insert([
            'announce_id'   => $params['announce_id'],
            'user_id'       => $params['user_id'],
            'status'        => $params['status'],
        ]);

        return $ret;
    }

    public function firstMail() {
        $ret = DB::table($this->table)
        ->where('status', 0)
        ->orderBy('id', 'ASC')
        ->first();

        return $ret;
    }

    public function updateRecord($params, $id) {
        $ret = DB::table($this->table)
        ->where('id', $id)
        ->update($params);

        return $ret;
    }

    public function usersByMailID($id) {
        $records = DB::table($this->table)
            ->leftJoin($this->user_table, function($leftJoin) {
                $leftJoin->on($this->table . '.user_id', '=', $this->user_table . '.id');
            })
            ->where($this->table . '.announce_id', $id)
            ->select($this->table . '.*', $this->user_table . '.userid')
            ->get();
        
        if (!isset($records) || count($records) == 0) {
            return [];
        }

        return $records;
    }
}
