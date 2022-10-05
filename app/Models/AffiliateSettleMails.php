<?php

namespace App\Models;

use DB;
use Log;
use Auth;
use Session;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AffiliateSettleMails extends Model
{
    protected $table = 'olc_affiliate_settle_mails';
    protected $table_users = 'olc_users';

    public function getSettleInfo($settle_id, &$count, &$sent, &$failed) {
        $count = self::where('settle_id', $settle_id)
            ->count();

        $sent = self::where('settle_id', $settle_id)
            ->where('is_sent', ANNOUNCE_STATUS_SENT)
            ->count();

        $failed = self::where('settle_id', $settle_id)
            ->where('is_sent', ANNOUNCE_STATUS_FAILED)
            ->count();
    }

    public static function insertRecords($settle_id, $records) {
        $ret = self::where('settle_id', $settle_id)
            ->delete();

        foreach ($records as $currency => $data) {
            $ret = self::insert([
                'settle_id'         => $settle_id,
                'userid'            => $data['userid'],
                'user_id'           => $data['user_id'],
                'is_sent'           => ANNOUNCE_STATUS_INIT,
            ]);
        }

        return $ret;
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->leftJoin($this->table_users, $this->table_users . '.id', '=', $this->table . '.user_id')
            ->select(
                $this->table . '.*',
                $this->table_users . '.nickname as nickname'
            );

        if (isset($params['settle_id'])) {
            $selector->where($this->table . '.settle_id', $params['settle_id']);
        }

        $total_count = $selector->count();

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

        return [
            'draw' => $params['draw']+0,
            'recordsTotal' => $total_count,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }
}
