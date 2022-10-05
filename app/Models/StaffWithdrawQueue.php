<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class StaffWithdrawQueue extends Model
{
    use Notifiable;
    protected $table = 'olc_staff_withdraw_queue';

    public function insertRecord($params, $withdrawId) {
        $ret = DB::table($this->table)
            ->insert([
                'currency'          => $params['currency'],
                'withdraw_id'       => $withdrawId,
                'cold_wallet_id'    => $params['cold_wallet_id'],
                'staff_id'          => $params['staff_id'],
                'to_address'        => $params['address'],
                'amount'            => $params['amount'],
                'status'            => WITHDRAW_QUEUE_STATUS_REQUESTED,
                'remark'            => $params['remark']
            ]);

        return $ret;
    }

    public static function updateRecords($where, $data) {
        $ret = self::where($where)
            ->update($data);

        return $ret;
    }

    public static function updateState($id, $tx_id, $status) {
        $ret = self::where('id', $id)
            ->update([
                'tx_id'     => $tx_id,
                'status'    => $status,
            ]);

        return $ret;
    }

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

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->where('status', '!=', WITHDRAW_QUEUE_STATUS_FINISHED);

        $totalCount = $selector->count();
        $selector->select($this->table . '.*');
        $recordsFiltered = $selector->count();
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
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }
}
