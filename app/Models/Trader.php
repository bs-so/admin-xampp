<?php

namespace App\Models;

use DB;
use DateTime;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Trader extends Authenticatable
{
    use Notifiable;
	protected $table = 'olc_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nickname', 'email', 'kyc_status', 'status', 'reged_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

    public static function getAll() {
        $records = self::whereIn('kyc_status', [USER_STATUS_BANNED, USER_STATUS_ACTIVE, USER_STATUS_REQUESTED, USER_STATUS_BLOCKED])
            ->get();

        if (!isset($records) || count($records) == 0) {
            return [];
        }

        return $records;
    }

    public function getTotalCount() {
        $count = self::where('status', STATUS_ACTIVE)
            ->count();

        return $count;
    }

    public function getAccessCount() {
        $count = self::where('session_id', '!=', '')->count();

        return $count;
    }

    public function getRegisterData($take) {
        $result = [];

        $today = date('Y-m-d');
        $date = new DateTime($today);
        for ($i = 1; $i < $take; $i ++) {
            $temp = $date->format('Y-m-d');
            $count = self::where('created_at', 'like', $temp . '%')
                ->count();

            $result[] = $count;
            $date = $date->add(date_interval_create_from_date_string('-1 day'));
        }

        return array_reverse($result);
    }

    public static function getNameById($id) {
        $record = self::where('id', $id)
            ->select('nickname')
            ->first();

        if (!isset($record) || !isset($record->nickname)) {
            return '';
        }

        return $record->nickname;
    }

    public static function getIDByUserID($userid) {
        $record = self::where('userid', $userid)
            ->select('id')
            ->first();

        if (!isset($record) || !isset($record->id)) {
            return 0;
        }

        return $record->id;
    }

    public static function getAllUserIds() {
        $records = self::where('status', STATUS_ACTIVE)
            ->select('id')
            ->get();

        if (!isset($records) || count($records) == 0) {
            return [];
        }

        return $records;
    }

    public function createRecord($record) {
        $result = DB::table($this->table)
            ->insert($record);

        return $result;
    }

    public function getRecordById($id) {
        $records = DB::table($this->table)
            ->where('id', $id)
            ->select('*')
            ->get();

        if (!isset($records) || count($records) == 0) {
            return [];
        }

        return $records[0];
    }

    public function getRecordByUserID($userid) {
        $record = DB::table($this->table)
            ->where('userid', $userid)
            ->select('*')
            ->first();

        return $record;
    }

    public function updateRecordById($id, $info) {
        $result = DB::table($this->table)
            ->where('id', $id)
            ->update($info);

        return $result;
    }

    public function updateStatus($params) {
        $ret = self::where('id', $params['id'])
            ->update([
                'status'    => $params['status'],
            ]);

        return $ret;
    }

    public function updateLang($id, $lang) {
        $ret = DB::table($this->table)
            ->where('id', $id)
            ->update([
                'lang'  => $lang,
            ]);

        return $ret;
    }

    public function deleteRecordById($id) {
        $records = DB::table($this->table)
            ->where('id', $id)
            ->get();
        if (!isset($records) || count($records) == 0) {
            return -1;
        }

        $selector = DB::table($this->table)
            ->where('id', $id)
            ->delete();

        return 1;
    }

    public function getTraderIdByUserID($userid) {
        $result = DB::table($this->table)
            ->where('userid', $userid)
            ->select('id')
            ->first();

        if (!isset($result) || !isset($result->id)) {
            return 0;
        }

        return $result->id;
    }

    public function getTraderIdsByUserID($userid) {
        $result = DB::table($this->table)
            ->where('userid', 'like', '%' . $userid . '%')
            ->select('id')
            ->get();

        if (!isset($result) || empty($result)) {
            return false;
        }

        $arr = array();
        foreach ($result as $index => $record) {
            $arr[] = $record->id;
        }

        return $arr;
    }

    public function getTraderIdsByName($name) {
        $result = DB::table($this->table)
            ->where('email', 'like', '%' . $name . '%')
            ->select('id')
            ->get();

        if (!isset($result) || empty($result)) {
            return false;
        }

        $arr = array();
        foreach ($result as $index => $record) {
            $arr[] = $record->id;
        }

        return $arr;
    }

    public function getTraderInfo($trader_id) {
        $records = DB::table($this->table)
            ->where('id', $trader_id)
            ->select('*')
            ->get();

        if (!isset($records) || count($records) == 0) {
            return false;
        }

        return $records[0];
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->select(
                '*'
            );

        // filtering
        if (isset($params['columns'][1]['search']['value'])
            && $params['columns'][1]['search']['value'] !== ''
        ) {
            $selector->where('userid', 'like', '%' . $params['columns'][1]['search']['value'] . '%');
        }
        if (isset($params['columns'][2]['search']['value'])
            && $params['columns'][2]['search']['value'] !== ''
        ) {
            $selector->where('nickname', 'like', '%' . $params['columns'][2]['search']['value'] . '%');
        }
        if (isset($params['columns'][3]['search']['value'])
            && $params['columns'][3]['search']['value'] !== ''
        ) {
            $selector->where('email', 'like', '%' . $params['columns'][3]['search']['value'] . '%');
        }
        if (isset($params['columns'][4]['search']['value'])
            && $params['columns'][4]['search']['value'] !== ''
        ) {
            $selector->where('kyc_status', $params['columns'][4]['search']['value']);
        }
        if (isset($params['columns'][5]['search']['value'])
            && $params['columns'][5]['search']['value'] !== ''
        ) {
            $selector->where('status', $params['columns'][5]['search']['value']);
        }
        if (isset($params['columns'][6]['search']['value'])
            && $params['columns'][6]['search']['value'] !== ''
        ) {
            $dateRange = preg_replace('/[\$\,]/', '', $params['columns'][6]['search']['value']);
            $elements = explode(':', $dateRange);

            if ($elements[0] != "" || $elements[1] != "") {
                $elements[0] .= ' 00:00:00';
                $elements[1] .= ' 23:59:59';
                $selector->whereBetween('created_at', $elements);
            }
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
            'recordsTotal' => DB::table($this->table)->count(),
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }

    public function getUsers($params) {
        $selector = DB::table($this->table)
            ->select('id');

        switch ($params['type']) {
            case '1':
                break;
            case '2':
                $amountRange = preg_replace('/[\$\,]/', '', $params['filterDates']);
                $elements = explode(':', $amountRange);

                if ($elements[0] != "" || $elements[1] != "") {
                    $elements[0] .= ' 00:00:00';
                    $elements[1] .= ' 23:59:59';
                    $selector->whereBetween('last_loginned', $elements);
                }
                break;
            case '3':
                $amountRange = preg_replace('/[\$\,]/', '', $params['filterDates']);
                $elements = explode(':', $amountRange);

                if ($elements[0] != "" || $elements[1] != "") {
                    $elements[0] .= ' 00:00:00';
                    $elements[1] .= ' 23:59:59';
                    $selector->whereBetween('created_at', $elements);
                }
                break;
            default:
                break;
        }

        $records = $selector->get()->pluck('id');
        return $records;
    }
}
