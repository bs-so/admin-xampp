<?php

namespace App\Models;

use DB;
use Auth;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
	protected $table = 'olc_staff';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'login_id', 'name', 'email', 'password', 'role', 'lang', 'avatar', 'status'
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
        'email_verified_at' => 'datetime',
    ];

    public static function getAll() {
        $records = self::where('status', STATUS_ACTIVE)
            ->whereIn('role', [USER_ROLE_ADMIN])
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

    public function updateRecordById($id, $info) {
        $result = DB::table($this->table)
            ->where('id', $id)
            ->update($info);

        return $result;
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
            ->select('role')
            ->get();
        if (!isset($records) || count($records) == 0) {
            return -1;
        }
        if ($records[0]->role == USER_ROLE_ADMIN) {
            return 0;
        }

        $selector = DB::table($this->table)
            ->where('id', $id)
            ->delete();

        return 1;
    }

    public function makeCsv($filename) {
        $csv = '';
        $titles = ['no', 'login_id', 'name', 'email', 'role', 'status', 'reged_at'];
        foreach ($titles as $index => $title) {
            $csv .= trans('staff.table.' . $title) . ',';
        }
        $csv = substr($csv, 0, strlen($csv) - 1) . "\n";

        $records = self::select('*')->get();
        foreach ($records as $index => $record) {
            $csv .= ($index + 1) . ',';
            $csv .= $record->login_id . ',';
            $csv .= $record->name . ',';
            $csv .= $record->email . ',';
            $csv .= g_enum('UserRoleData')[$record->role][0] . ',';
            $csv .= g_enum('StatusData')[$record->status][0] . ',';
            $csv .= $record->created_at . "\n";
        }

        file_put_contents($filename, "\xEF\xBB\xBF". $csv);
    }

    public function getForDatatable($params) {
        $user = Auth::user();

        $selector = DB::table($this->table)
            ->where('role', '>=', $user->role)
            ->select(
                '*'
            );
        $totalCount = $selector->count();

        // filtering
        if (isset($params['columns'][1]['search']['value'])
            && $params['columns'][1]['search']['value'] !== ''
        ) {
            $selector->where('login_id', 'like', '%' . $params['columns'][1]['search']['value'] . '%');
        }
        if (isset($params['columns'][2]['search']['value'])
            && $params['columns'][2]['search']['value'] !== ''
        ) {
            $selector->where('name', 'like', '%' . $params['columns'][2]['search']['value'] . '%');
        }
        if (isset($params['columns'][4]['search']['value'])
            && $params['columns'][4]['search']['value'] !== ''
        ) {
            $selector->where('role', $params['columns'][4]['search']['value']);
        }
        if (isset($params['columns'][5]['search']['value'])
            && $params['columns'][5]['search']['value'] !== ''
        ) {
            $selector->where('status', $params['columns'][5]['search']['value']);
        }
        if (isset($params['columns'][6]['search']['value'])
            && $params['columns'][6]['search']['value'] !== ''
        ) {
            $amountRange = preg_replace('/[\$\,]/', '', $params['columns'][6]['search']['value']);
            $elements = explode(':', $amountRange);

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
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }

    
}
