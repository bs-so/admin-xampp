<?php

namespace App\Models;

use DB;
use DateTime;
use Litipk\BigNumbers\Decimal;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Profits extends Model
{
    use Notifiable;
    protected $table = 'olc_profits';
    protected $table_user = 'olc_users';
    protected $table_user_withdraw = 'olc_users_withdraw';

    public function makeCsv($filename, $currency) {
        $csv = '';
        $titles = ['no', 'currency', 'profit', 'user', 'type', 'occurred_at'];
        foreach ($titles as $index => $title) {
            $csv .= trans('statistics.profits.' . $title) . ',';
        }
        $csv = substr($csv, 0, strlen($csv) - 1) . "\n";

        $records = DB::table($this->table)
            ->leftJoin($this->table_user, $this->table_user . '.id', '=', $this->table . '.user_id')
            ->where('currency', $currency)
            ->select(
                $this->table . '.*',
                $this->table_user . '.name as user_name'
            )
            ->get();
        foreach ($records as $index => $record) {
            $csv .= ($index + 1) . ',';
            $csv .= $currency . ',';
            $csv .= $record->profit . ',';
            $csv .= $record->user_name . ',';
            $csv .= g_enum('ProfitTypeData')[$record->type][0] . ',';
            $csv .= $record->created_at . "\n";
        }

        file_put_contents($filename, "\xEF\xBB\xBF". $csv);
    }

    public function insertRecord($params) {
        $profit = Decimal::create(isset($params['profit']) ? $params['profit'] : 0);
        if ($profit->isZero()) {
            // No profit
            return;
        }

        $ret = self::insert($params);

        return $ret;
    }

    public function addCryptoProfits($params) {
        $records = DB::table($this->table_user_withdraw)
            ->whereIn('id', $params['selected'])
            ->groupBy('user_id')
            ->select('user_id', 'currency', DB::raw('sum(withdraw_fee) as profit'))
            ->get();

        if (!isset($records) || count($records) == 0) {
            return false;
        }

        $ret = true;
        foreach ($records as $index => $record) {
            $ret = self::insert([
                'currency'      => $record->currency,
                'profit'        => $record->profit,
                'user_id'       => $record->user_id,
                'type'          => PROFIT_TYPE_WITHDRAW,
            ]);
        }

        return $ret;
    }

    public function getTotalData() {
        // Get monthly data
        $records = DB::table($this->table)
            ->groupBy('currency')
            ->groupBy(DB::raw('substr(created_at, 1, 7)'))
            ->select(
                'currency',
                DB::raw('substr(created_at, 1, 7) as stat_date'),
                DB::raw('sum(profit) as total_profit')
            )
            ->orderBy(DB::raw('substr(created_at, 1, 7)'), 'asc')
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

    public function getMonthData($sel_date, $sel_currency) {
        // Get monthly data
        $records = DB::table($this->table)
            ->where('created_at', 'like', $sel_date . '%')
            ->where('currency', $sel_currency)
            ->groupBy('created_at')
            ->orderBy('created_at', 'asc')
            ->select(
                DB::raw('substr(created_at, 1, 10) as date'),
                DB::raw('sum(profit) as total_profit')
            )
            ->pluck('total_profit', 'date');

        if (!isset($records) || count($records) == 0) {
            return [];
        }

        return $records;
    }

    public function getSummaryData($currency) {
        $records = self::where('currency', $currency)
            ->groupBy('type')
            ->groupBy(DB::raw('MID(created_at, 1, 7)'))
            ->orderBy(DB::raw('MID(created_at, 1, 7)'), 'desc')
            ->select(
                DB::raw('sum(profit) as total_profit'),
                DB::raw('MID(created_at, 1, 7) as date'),
                'type'
            )
            ->get();

        $result = array();
        foreach ($records as $index => $record) {
            $date = $record->date;
            $profit = Decimal::create($record->total_profit);
            if (!isset($result[$date])) {
                $result[$date] = array(
                    'total'     => 0,
                );
            }
            $result[$date][$record->type] = $profit->__toString();
            $result[$date]['total'] += Decimal::create($result[$date]['total'])->add($profit)->__toString();
        }

        return $result;
    }

    public function getChartData($currency, $take) {
        $result = [];
        $date = new DateTime(date('Y-m-d'));
        for ($i = 0; $i < $take; $i ++) {
            $record = self::where('currency', $currency)
                ->where('created_at', 'like', $date->format('Y-m') . '%')
                ->select(DB::raw('sum(profit) as total_profit'))
                ->first();

            if (!isset($record) || !isset($record->total_profit)) {
                $result[]= 0;
            }
            else {
                $result[]= $record->total_profit;
            }
            $date = $date->add(date_interval_create_from_date_string('-1 month'));
        }

        return array_reverse($result);
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->leftJoin($this->table_user, $this->table_user . '.id', '=', $this->table . '.user_id')
            ->where($this->table . '.currency', $params['currency'])
            ->where($this->table . '.created_at', 'like', $params['date'] . '%')
            ->select(
                $this->table . '.*',
                $this->table_user . '.userid as userid',
                $this->table_user . '.nickname as nickname'
            );

        $recordsTotal = $selector->count();

        // filtering
        if (isset($params['columns'][3]['search']['value'])
            && $params['columns'][3]['search']['value'] !== ''
        ) {
            $selector->where('type', $params['columns'][3]['search']['value']);
        }
        if (isset($params['columns'][4]['search']['value'])
            && $params['columns'][4]['search']['value'] !== ''
        ) {
            $amountRange = preg_replace('/[\$\,]/', '', $params['columns'][4]['search']['value']);
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
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }
}
