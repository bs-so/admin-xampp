<?php

namespace App\Models;

use DB;
use Log;
use Auth;
use Litipk\BigNumbers\Decimal;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class SystemProfit extends Model
{
    protected $table = 'olc_system_profits';
    protected $table_users = 'olc_users';

    public function insertRecord($params, $type) {
        $profit = Decimal::create($params['profit']);
        if ($profit->isZero()) {
            return true;
        }

        $ret = self::insert([
            'date'      => date('Y-m-d'),
            'type'      => $type,
            'user_id'   => $params['user_id'],
            'currency'  => $params['currency'],
            'profit'    => $params['profit'],
        ]);

        return $ret;
    }

    public function getTotalData($type) {
        // Get monthly data
        $records = DB::table($this->table)
            ->where('type', $type)
            ->groupBy('currency')
            ->groupBy(DB::raw('substr(date, 1, 7)'))
            ->select(
                'currency',
                DB::raw('substr(date, 1, 7) as stat_date'),
                DB::raw('sum(profit) as total_profit')
            )
            ->orderBy(DB::raw('substr(date, 1, 7)'), 'asc')
            ->get();

        $result = array();
        foreach ($records as $index => $record) {
            $currency = $record->currency;
            $stat_date = $record->stat_date;
            if (!isset($result[$stat_date])) {
                $result[$stat_date] = array();
            }

            $result[$stat_date][$currency] = $record->total_profit;
        }

        return $result;
    }

    public function getMonthData($sel_date, $sel_currency, $sel_type) {
        // Get monthly data
        $records = DB::table($this->table)
            ->where('date', 'like', $sel_date . '%')
            ->where('currency', $sel_currency)
            ->where('type', $sel_type)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->select(
                'date',
                DB::raw('sum(profit) as total_profit')
            )
            ->pluck('total_profit', 'date');

        if (!isset($records) || count($records) == 0) {
            return [];
        }

        return $records;
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->leftJoin($this->table_users, $this->table_users . '.id', '=', $this->table . '.user_id')
            ->select(
                $this->table . '.*',
                $this->table_users . '.userid',
                $this->table_users . '.nickname'
            );

        if (isset($params['date'])) {
            $selector->where('date', $params['date']);
        }
        if (isset($params['currency'])) {
            $selector->where('currency', $params['currency']);
        }
        if (isset($params['type'])) {
            $selector->where('type', $params['type']);
        }

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

        return [
            'draw' => $params['draw']+0,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }
}
