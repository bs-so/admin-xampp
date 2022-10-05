<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Trader;
use Litipk\BigNumbers\Decimal;
use Log;

class Deposit extends Model
{
    protected $table        = 'olc_users_deposit';
    protected $table_queue  = 'olc_users_deposit_queue';
    protected $table_logs   = 'olc_realtime_logs';

    protected $fillable = [];

    public function getUserDepositData($currencies) {
        $result = array();
        $today = date('Y-m-d');

        // Init
        foreach ($currencies as $index => $currency) {
            $result[$currency->currency] = array(
                'last_updated'      => date('Y-m-d H:i:s'),
                'queue_count'       => 0,
                'today_count'       => 0,
                'today_amount'      => 0,
            );
        }

        // Queue Status
        $records = DB::table($this->table_queue)
            ->select('*')
            ->get();
        if (isset($records) && count($records) > 0) {
            foreach ($currencies as $index => $currency) {
                $result[$currency->currency]['queue_count'] = count($records);
            }
        }

        // Realtime Logs
        $records = DB::table($this->table_logs)
            ->where('type', LOGS_TYPE_USER_DEPOSIT)
            ->select('currency', 'last_updated')
            ->get();
        foreach ($records as $index => $record) {
            $currency = $record->currency;
            $result[$currency]['last_updated'] = $records[0]->last_updated;
        }

        // Today Result
        $records = DB::table($this->table)
            ->where('status', STATUS_ACTIVE)
            ->where('created_at', 'like', '%' . $today . '%')
            ->groupBy('currency')
            ->select(
                'currency',
                DB::raw('count(id) as total_count'),
                DB::raw('sum(amount) as total_amount')
            )
            ->get();
        foreach ($records as $index => $record) {
            $currency = $record->currency;
            $result[$currency]['today_count'] = $record->total_count;
            $result[$currency]['today_amount'] = $record->total_amount;
        }

        return $result;
    }
}
