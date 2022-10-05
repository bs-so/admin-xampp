<?php

namespace App\Models;

use DB;
use Log;
use DateTime;
use Litipk\BigNumbers\Decimal;
use Illuminate\Database\Eloquent\Model;

class RealtimeLogs extends Model
{
    protected $table = 'olc_realtime_logs';

    public function insertRecord($type, $currency, $last_updated = '') {
        if ($last_updated == '') {
            $last_updated = date('Y-m-d H:i:s');
        }

        $records = DB::table($this->table)
            ->where('type', $type)
            ->where('currency', $currency)
            ->select('*')
            ->get();

        $ret = true;
        if (!isset($records) || count($records) == 0) {
            $ret = DB::table($this->table)
                ->insert([
                    'type'          => $type,
                    'currency'      => $currency,
                    'last_updated'  => $last_updated,
                ]);
        }
        else {
            $ret = DB::table($this->table)
                ->where('id', $records[0]->id)
                ->update([
                    'last_updated'  => $last_updated,
                ]);
        }

        return $ret;
    }
}
