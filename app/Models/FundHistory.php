<?php

namespace App\Models;

use Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Trader;
use Illuminate\Foundation\Auth\User as Authenticatable;

class FundHistory extends Model
{
    protected $table_staff_deposit      = 'olc_staff_deposit';
	protected $table_staff_withdraw     = 'olc_staff_withdraw';
    protected $table_logs               = 'olc_realtime_logs';

    protected $fillable = [];

    public function getManagerDepositData($currencies) {
        $result = array();
        $today = date('Y-m-d');

        foreach ($currencies as $index => $currency) {
            $result[$currency->currency] = array(
                'last_updated'      => date('Y-m-d H:i:s'),
                'queue_count'       => 0,
                'queue_amount'      => 0,
                'today_count'       => 0,
                'today_amount'      => 0,
            );
        }

        // Realtime Logs
        $records = DB::table($this->table_logs)
            ->where('type', LOGS_TYPE_MANAGER_DEPOSIT)
            ->select('currency', 'last_updated')
            ->get();
        foreach ($records as $index => $record) {
            $currency = $record->currency;
            $result[$currency]['last_updated'] = $records[0]->last_updated;
        }

        // Queue data
        $records = DB::table($this->table_staff_deposit)
//            ->where('trans_type', TRANSFER_STATUS_REQUESTED)
            ->where('status', TRANSFER_STATUS_SENT)
            ->groupBy('currency')
            ->select(
                'currency',
                DB::raw('count(id) as total_count'),
                DB::raw('sum(amount) as total_amount')
            )
            ->get();
        foreach ($records as $index => $record) {
            $currency = $record->currency;
            $result[$currency]['queue_count'] = $record->total_count;
            $result[$currency]['queue_amount'] = $record->total_amount;
        }

        // Today data
        $records = DB::table($this->table_staff_deposit)
            ->where('status', STATUS_ACCEPTED)
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

	public function getManagerWithdrawData($currencies) {
		$result = array();
		$today = date('Y-m-d');

		foreach ($currencies as $index => $currency) {
			$result[$currency->currency] = array(
				'last_updated'      => date('Y-m-d H:i:s'),
				'queue_count'       => 0,
				'queue_amount'      => 0,
				'today_count'       => 0,
				'today_amount'      => 0,
			);
		}

		// Realtime Logs
		$records = DB::table($this->table_logs)
			->where('type', LOGS_TYPE_MANAGER_WITHDRAW)
			->select('currency', 'last_updated')
			->get();
		foreach ($records as $index => $record) {
			$currency = $record->currency;
			$result[$currency]['last_updated'] = $records[0]->last_updated;
		}

		// Queue data
		$records = DB::table($this->table_staff_withdraw)
			->where('status', '!=', STATUS_ACTIVE)
			->groupBy('currency')
			->select(
				'currency',
				DB::raw('count(id) as total_count'),
				DB::raw('sum(amount) as total_amount')
			)
			->get();
		foreach ($records as $index => $record) {
			$currency = $record->currency;
			$result[$currency]['queue_count'] = $record->total_count;
			$result[$currency]['queue_amount'] = $record->total_amount;
		}

		// Today data
		$records = DB::table($this->table_staff_withdraw)
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
