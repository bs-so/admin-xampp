<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Identity extends Model
{
    use Notifiable;
	protected $table = 'olc_identities';
	protected $table_user = 'olc_users';

    public function makeCsv($filename, $trader_id = 0) {
        $csv = '';
        $titles = ['no', 'email', 'userid', 'nickname', 'gender', 'mobile', 'status', 'reged_at'];
        foreach ($titles as $index => $title) {
            $csv .= trans('requests.kyc.' . $title) . ',';
        }
        $csv = substr($csv, 0, strlen($csv) - 1) . "\n";

        $records = DB::table($this->table_user)
            ->whereIn('kyc_status', [KYC_STATUS_REQUESTED, KYC_STATUS_ACTIVE])
            ->select('*')
            ->get();
        foreach ($records as $index => $record) {
            $csv .= ($index + 1) . ',';
            $csv .= $record->email . ',';
            $csv .= $record->userid . ',';
            $csv .= $record->nickname . ',';
            $csv .= g_enum('UserGenderData')[$record->gender][0] . ',';
            $csv .= $record->mobile . ',';
            $csv .= g_enum('KycStatusData')[$record->kyc_status][0] . ',';
            $csv .= $record->created_at . "\n";
        }

        file_put_contents($filename, "\xEF\xBB\xBF". $csv);
    }

	public function getPhotoUrl($id) {
	    $record = self::where('id', $id)
            ->select('photo_url')
            ->first();

	    if (!isset($record) || !isset($record->photo_url)) {
	        return '';
        }

	    return $record->photo_url;
    }

	public function updateStatus($params) {
	    $ret = DB::table($this->table_user)
            ->where('id', $params['id'])
            ->update([
                'kyc_status'    => $params['status'],
            ]);

	    return $ret;
    }

    public function getUserList($params) {
        $selector = DB::table($this->table_user)
            ->whereIn('kyc_status', [KYC_STATUS_REQUESTED, KYC_STATUS_ACTIVE])
            ->select(
                '*'
            );
        $recordsTotal = $selector->count();

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
        if (isset($params['columns'][6]['search']['value'])
            && $params['columns'][6]['search']['value'] !== ''
        ) {
            $selector->where('kyc_status', $params['columns'][6]['search']['value']);
        }
        if (isset($params['columns'][7]['search']['value'])
            && $params['columns'][7]['search']['value'] !== ''
        ) {
            $dateRange = preg_replace('/[\$\,]/', '', $params['columns'][7]['search']['value']);
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
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }

    public function getIdentityList($params) {
        $selector = DB::table($this->table)
            ->where('user_id', $params['user_id'])
            ->select(
                '*'
            );
        $recordsTotal = $selector->count();

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
        foreach ($records as $index => $record) {
            $url = $record->photo_url;

            $file = $url;
            $dest = 'uploads/temp/temp' . $index;
            copy($file, $dest);

            $record->filesize = filesize($dest);
        }

        return [
            'draw' => $params['draw']+0,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }
}
