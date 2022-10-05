<?php

namespace App\Models;

use DB;
use Log;
use Auth;
use Session;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AffiliateSettleCommission extends Model
{
    protected $table = 'olc_affiliate_settle_commissions';
    protected $table_users = 'olc_users';

    public function getUserCommissions($settle_id, $user_id) {
        $records = self::where('settle_id', $settle_id)
            ->where('user_id', $user_id)
            ->select('currency', 'commission')
            ->get();

        if (!isset($records) || count($records) == 0) {
            return [];
        }

        return $records;
    }

    public function saveCommission($settle_info) {
        $settle_id = $settle_info['new_settle_id'];
        $commissions = Session::get('affiliate_settle_commissions');

        $ret = self::where('settle_id', $settle_id)
            ->delete();

        foreach ($commissions as $user_id => $records) {
            foreach ($records as $currency => $data) {
                $ret = self::insert([
                    'settle_id'     => $settle_id,
                    'userid'        => $data['userid'],
                    'user_id'       => $data['user_id'],
                    'currency'      => $data['currency'],
                    'commission'    => $data['commission'],
                    'settle_status' => ENTRY_SETTLE_STATUS_NONE,
                ]);
            }
        }

        return $ret;
    }

    public static function updateSettleStatus($settle_id, $settle_status) {
        $ret = self::where('settle_id', $settle_id)
            ->where('settle_status', ENTRY_SETTLE_STATUS_NONE)
            ->update([
                'settle_status'     => $settle_status,
            ]);

        return $ret;
    }

    public function getForDatatable($params) {
        // 1. Get user ids
        $settle_id = $params['settle_id'];
        $selector = DB::table($this->table)
            ->where('settle_id', $settle_id)
            ->groupBy('user_id')
            ->select('user_id');
        if (isset($params['settle_status'])) {
            $selector->where($this->table . '.settle_status', $params['settle_status']);
        }

        $total_count = $selector->count();
        // offset & limit
        if (!empty($params['start']) && $params['start'] > 0) {
            $selector->skip($params['start']);
        }
        if (!empty($params['length']) && $params['length'] > 0) {
            $selector->take($params['length']);
        }
        $recordsFiltered = $selector->count();

        $user_ids = $selector->pluck('user_id');

        $selector = DB::table($this->table)
            ->leftJoin($this->table_users, $this->table_users . '.id', '=', $this->table . '.user_id')
            ->whereIn('user_id', $user_ids)
            ->where('settle_id', $settle_id)
            ->groupBy('user_id')
            ->groupBy('currency')
            ->select(
                'user_id', $this->table . '.userid', 'currency', 'commission', $this->table . '.created_at',
                $this->table_users . '.nickname'
            );

        if (isset($params['settle_status'])) {
            $selector->where($this->table . '.settle_status', $params['settle_status']);
        }

        // 2. Get records
        $records = $selector->get();
        $crypto_settings = Session::get('crypto_settings');

        $settleTbl = new AffiliateSettle();
        $prev_settle_id = $settleTbl->getPrevSettleId($settle_id);

        $result = array();
        $count = 0;
        $indicies = array();
        foreach ($records as $index => $record) {
            $user_id = $record->user_id;
            $currency = $record->currency;
            if (!isset($indicies[$user_id])) {
                $result[$count] = array(
                    'id'            => $index,
                    'userid'        => $record->userid,
                    'nickname'      => $record->nickname,
                    'created_at'    => $record->created_at,
                );
                $indicies[$user_id] = $count;
                $count ++;
            }

            $record_index = $indicies[$user_id];
            $data = $crypto_settings[$currency];
            $result[$record_index]['curr_' . $currency] = _number_format($record->commission, min(MINIMUM_BALANCE_DECIMALS, $data['rate_decimals']));
            $result[$record_index]['prev_' . $currency] = 0;
            if ($prev_settle_id > 0) {
                $temp = DB::table($this->table)
                    ->where('settle_id', $prev_settle_id)
                    ->where('user_id', $user_id)
                    ->where('currency', $currency)
                    ->select('commission')
                    ->first();

                $commission = (isset($temp->commission) ? $temp->commission : 0);
                $result[$record_index]['prev_' . $currency] = _number_format($commission, min(MINIMUM_BALANCE_DECIMALS, $data['rate_decimals']));
            }
        }

        return [
            'draw' => $params['draw']+0,
            'recordsTotal' => $total_count,
            'recordsFiltered' => $recordsFiltered,
            'data' => $result,
            'error' => 0,
        ];
    }
}
