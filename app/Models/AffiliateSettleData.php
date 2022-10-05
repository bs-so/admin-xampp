<?php

namespace App\Models;

use DB;
use Log;
use Auth;
use Session;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AffiliateSettleData extends Model
{
    protected $table = 'olc_affiliate_settle_data';
    protected $table_users = 'olc_users';

    public static function updateSettleStatus($settle_id, $settle_status) {
        $ret = self::where('settle_id', $settle_id)
            ->where('settle_status', ENTRY_SETTLE_STATUS_NONE)
            ->update([
                'settle_status'     => $settle_status,
            ]);

        return $ret;
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->select(
                $this->table . '.*'
            );

        if (isset($params['settle_id'])) {
            $selector->where($this->table . '.settle_id', $params['settle_id']);
        }
        if (isset($params['settle_status'])) {
            $selector->where($this->table . '.settle_status', $params['settle_status']);
        }

        $total_count = $selector->count();

        // filtering
        if (isset($params['columns'][1]['search']['value'])
            && $params['columns'][1]['search']['value'] !== ''
        ) {
            $selector->where($this->table . '.order_id', 'like', '%' . $params['columns'][1]['search']['value'] . '%');
        }
        if (isset($params['columns'][2]['search']['value'])
            && $params['columns'][2]['search']['value'] !== ''
        ) {
            $selector->where($this->table_users . '.name', 'like', '%' . $params['columns'][2]['search']['value'] . '%');
        }
        if (isset($params['columns'][3]['search']['value'])
            && $params['columns'][3]['search']['value'] !== ''
        ) {
            $selector->where($this->table_recruits . '.id', $params['columns'][3]['search']['value']);
        }
        if (isset($params['columns'][4]['search']['value'])
            && $params['columns'][4]['search']['value'] !== ''
        ) {
            $selector->where($this->table_products . '.id', $params['columns'][4]['search']['value']);
        }
        if (isset($params['columns'][5]['search']['value'])
            && $params['columns'][5]['search']['value'] !== ''
        ) {
            $dateRange = preg_replace('/[\$\,]/', '', $params['columns'][5]['search']['value']);
            $elements = explode(':', $dateRange);

            if ($elements[0] != "" || $elements[1] != "") {
                $elements[0] .= ' 00:00:00';
                $elements[1] .= ' 23:59:59';
                $selector->whereBetween($this->table . '.ordered_at', $elements);
            }
        }
        if (isset($params['columns'][7]['search']['value'])
            && $params['columns'][7]['search']['value'] !== ''
        ) {
            $selector->where($this->table . '.currency', $params['columns'][7]['search']['value']);
        }
        if (isset($params['columns'][9]['search']['value'])
            && $params['columns'][9]['search']['value'] !== ''
        ) {
            $selector->where($this->table . '.status', $params['columns'][9]['search']['value']);
        }

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
