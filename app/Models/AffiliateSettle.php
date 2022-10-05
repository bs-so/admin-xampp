<?php

namespace App\Models;

use DB;
use Log;
use Auth;
use Session;
use Litipk\BigNumbers\Decimal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AffiliateSettle extends Model
{
    protected $table = 'olc_affiliate_settle';
    protected $table_settle_data = 'olc_affiliate_settle_data';
    protected $table_users = 'olc_users';
    protected $table_staff = 'olc_staff';

    protected static $_summaries = array();
    protected static $_commissions = array();
    protected static $_user_balances = array();
    protected static $_announces = array();
    protected static $_settle_info = array();

    public function getRecordById($id) {
        $records = DB::table($this->table)
            ->leftJoin($this->table_staff, $this->table_staff . '.id', '=', $this->table . '.staff_id')
            ->where($this->table . '.id', $id)
            ->select(
                $this->table . '.*',
                $this->table_staff . '.name as operator'
            )
            ->get();

        if (!isset($records) || count($records) == 0) {
            return [];
        }

        return $records[0];
    }

    public function getLastRecord() {
        $record = DB::table($this->table)
            ->orderBy('id', 'desc')
            ->first();

        return $record;
    }

    public function getPrevSettleId($settle_id, $status = 0) {
        $selector = DB::table($this->table)
            ->where('id', '<', $settle_id)
            ->orderBy('id', 'desc')
            ->select('id');

        if ($status != 0) {
            $selector->where('status', $status);
        }

        $record = $selector->first();

        if (!isset($record) || !isset($record->id)) {
            return 0;
        }

        return $record->id;
    }

    public function checkPrevSettle() {
        $records = DB::table($this->table)
            ->where('status', '!=', AFFILIATE_SETTLE_STATUS_FINISHED)
            ->select('id')
            ->get();

        if (!isset($records) || count($records) == 0) {
            return true;
        }

        return false;
    }

    public function insertRecord($staff_id) {
        $new_id = DB::table($this->table)
            ->insertGetId([
                'staff_id'          => $staff_id,
                'remark'            => '',
                'status'            => AFFILIATE_SETTLE_STATUS_LOAD_CSV,
            ]);

        return $new_id;
    }

    public function updateStatus($id, $status) {
        $ret = self::where('id', $id)
            ->update([
                'status'    => $status,
            ]);

        return $ret;
    }

    public function updateBasic($id, $params) {
        $ret = self::where('id', $id)
            ->update([
                'begin_date'    => $params['begin_date'],
                'end_date'      => $params['end_date'],
            ]);

        return $ret;
    }

    public function deleteRecord($id) {
        $ret = self::where('id', $id)
            ->delete();

        return $ret;
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->leftJoin($this->table_staff, $this->table_staff . '.id', '=', $this->table . '.staff_id')
            ->select(
                $this->table . '.*',
                $this->table_staff . '.name as operator'
            );

        $recordsTotal = $selector->count();

        // filtering
        if (isset($params['columns'][5]['search']['value'])
            && $params['columns'][5]['search']['value'] !== ''
        ) {
            $selector->where('use_announce', $params['columns'][5]['search']['value']);
        }
        if (isset($params['columns'][6]['search']['value'])
            && $params['columns'][6]['search']['value'] !== ''
        ) {
            $selector->where($this->table . '.status', $params['columns'][6]['search']['value']);
        }
        if (isset($params['columns'][7]['search']['value'])
            && $params['columns'][7]['search']['value'] !== ''
        ) {
            $amountRange = preg_replace('/[\$\,]/', '', $params['columns'][7]['search']['value']);
            $elements = explode(':', $amountRange);

            if ($elements[0] != "" || $elements[1] != "") {
                $elements[0] .= ' 00:00:00';
                $elements[1] .= ' 23:59:59';
                $selector->whereBetween($this->table . '.created_at', $elements);
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
        $tbl = new AffiliateSettleMails();
        foreach ($records as $index => $record) {
            $record->announce_count = 0;
            $record->announce_sent = 0;
            $record->announce_failed = 0;
            if ($record->use_announce == MAIL_ANNOUNCE_YES) {
                $tbl->getSettleInfo($record->id, $record->announce_count, $record->announce_sent, $record->announce_failed);
            }
        }

        return [
            'draw' => $params['draw']+0,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }

    public function checkCsvData($filename) {
        $data = file_get_contents($filename);
        $records = explode("\r\n", $data);

        $result = array(
            'balance'   => [],
            'user'      => [],
        );
        $traderTbl = new Trader();
        foreach ($records as $index => $record) {
            $items = explode('"', $record);
            if ($index == 0 || count($items) <= 1) continue;
            $userid = $items[1];
            $currency = $items[3];
            $amount = Decimal::create($items[5] == '' ? 0 : $items[5]);

            if (!isset($result['balance'][$currency])) {
                $result['balance'][$currency] = array(
                    'csv_sum'   => $amount->__toString(),
                    'balance'   => 0,
                );
            }
            else {
                $temp = Decimal::create($result['balance'][$currency]['csv_sum']);
                $temp = $temp->add($amount);
                $result['balance'][$currency]['csv_sum'] = $temp->__toString();
            }

            $ret = $traderTbl->getTraderIdByUserID($userid);
            if ($ret == 0) {
                $result['user'][] = $userid;
            }
        }

        $balanceTbl = new SystemBalance();
        foreach ($result['balance'] as $currency => $data) {
            $balance = $balanceTbl->getBalance(SYSTEM_BALANCE_TYPE_AFFILIATE, $currency);
            $result['balance'][$currency]['balance'] = $balance;
            if (Decimal::create($balance)->isLessThan(Decimal::create($result['balance'][$currency]['csv_sum']))) {
                $result['balance'][$currency]['result'] = 1; // Not enough balance
            }
            else {
                $result['balance'][$currency]['result'] = 0; // Enough balance
            }
        }

        return $result;
    }

    public function saveCsvData($filename, $settle_id) {
        $data = file_get_contents($filename);
        $records = explode("\r\n", $data);

        $ret = DB::table($this->table_settle_data)
            ->where('settle_id', $settle_id)
            ->delete();

        $csv_data = array();
        foreach ($records as $index => $record) {
            if ($index == 0) continue;
            $data = explode("\"", $record);
            if (count($data) <= 1) continue;
            $userid = $data[1];
            $currency = $data[3];
            $amount = $data[5];
            $csv_data[] = array(
                'userid'    => $userid,
                'currency'  => $currency,
                'amount'    => $amount,
            );
            $ret = DB::table($this->table_settle_data)
                ->insert([
                    'settle_id'     => $settle_id,
                    'userid'        => $userid,
                    'currency'      => $currency,
                    'amount'        => $amount,
                    'settle_status' => ENTRY_SETTLE_STATUS_NONE,
                ]);
        }

        $settle_info = Session::get('affiliate_settle_info');
        $settle_info['csv_data'] = $csv_data;
        Session::put('affiliate_settle_info', $settle_info);

        return $ret;
    }

    public function loadSettleData($settle_info, $crypto_settings) {
        // Initialize
        self::$_settle_info = $settle_info;
        self::$_summaries = array();
        $tbl = new SystemBalance();
        foreach ($crypto_settings as $currency => $data) {
            $balance = $tbl->getBalance(SYSTEM_BALANCE_TYPE_AFFILIATE, $currency);
            self::$_summaries[$currency] = array(
                'system_balance'    => $balance,
                'total_user'        => 0,
                'total_commission'  => 0,
                'users'             => array(),
            );
        }

        self::$_commissions = array();
        self::$_user_balances = array();
        self::$_announces = array();

        // Save to session
        Session::put('affiliate_settle_info', self::$_settle_info);
        Session::put('affiliate_settle_user_balances', self::$_user_balances);
        Session::put('affiliate_settle_summaries', self::$_summaries);
        Session::put('affiliate_settle_commissions', self::$_commissions);
        Session::put('affiliate_settle_announces', self::$_announces);

        return true;
    }

    public function calcCommission($settle_info, $step, &$summaries) {
        Log::channel('settle')->info(">> Step (" . $step . ") has started!");

        // Load data from session
        self::$_settle_info = Session::get('affiliate_settle_info');
        self::$_summaries = Session::get('affiliate_settle_summaries');
        self::$_commissions = Session::get('affiliate_settle_commissions');
        self::$_user_balances = Session::get('affiliate_settle_user_balances');
        self::$_announces = Session::get('affiliate_settle_announces');

        $settle_id = $settle_info['new_settle_id'];
        $crypto_settings = Session::get('crypto_settings', []);

        // 1. Get settle orders
        $process_count = 0;
        $traderTbl = new Trader();
        $csv_data = self::$_settle_info['csv_data'];
        Log::channel('settle')->info("    CSV Data Count : " . count($csv_data));
        foreach ($csv_data as $entry_index => $entry) {
            //if ($order_index < self::$_process_order_count) continue;
            $process_count ++;

            // Calculate commission for order
            $userid = $entry['userid'];
            $currency = $entry['currency'];
            $amount = Decimal::create($entry['amount']);
            $user = $traderTbl->getRecordByUserID($userid);

            if (!in_array($user->id, self::$_summaries[$currency]['users'])) {
                self::$_summaries[$currency]['users'][] = $user->id;
                self::$_summaries[$currency]['total_user'] ++;
            }
            self::$_summaries[$currency]['total_commission'] = Decimal::create(self::$_summaries[$currency]['total_commission'])->add($amount)->__toString();

            Log::channel('settle')->info("     #### Entry " . $userid . ", " . $user->nickname . ", " . $currency . ", " . $amount);
            $ret = $this->calcIBProfits($user->id, $userid, $currency, $amount);

            //if ($process_count >= self::$_process_order_size) break;
        }

        //self::$_process_order_count += $process_count;
        Log::channel('settle')->info("   Step (" . $step . ") has finished!");
        Log::channel('settle')->info("");

        // Save to session
        Session::put('affiliate_settle_info', self::$_settle_info);
        Session::put('affiliate_settle_summaries', self::$_summaries);
        Session::put('affiliate_settle_commissions', self::$_commissions);
        Session::put('affiliate_settle_user_balances', self::$_user_balances);
        Session::put('affiliate_settle_announces', self::$_announces);

        //if ($process_count >= self::$_process_order_size) return false;

        // Calculate percent
        foreach (self::$_summaries as $currency => $data) {
            $total = Decimal::create(self::$_summaries[$currency]['system_balance']);
            $percent = Decimal::create(0);
            if (!$total->isZero()) {
                $percent = Decimal::create(self::$_summaries[$currency]['total_commission']);
                $percent = $percent->div($total);
                $percent = $percent->mul(Decimal::create(100));
            }
            self::$_summaries[$currency]['percent'] = $percent->__toString();
        }

        Log::channel('settle')->info("     ===================================================");
        Log::channel('settle')->info("     ---------------- Total Commission  ----------------");
        foreach (self::$_summaries as $currency => $data) {
            $decimals = $crypto_settings[$currency]['rate_decimals'];
            Log::channel('settle')->info("     " . $currency . " : " . _number_format($data['system_balance'], $decimals) . ", " .
                _number_format($data['total_commission'], $decimals) . ", " .
                _number_format($data['percent'], 2));
        }
        Log::channel('settle')->info("     ---------------------------------------------------");

        Session::put('affiliate_settle_summaries', self::$_summaries);
        $summaries = self::$_summaries;

        Log::channel('settle')->info(">> Calculate commission has finished!!!");
        Log::channel('settle')->info("");

        return true;
    }

    public function calcIBProfits($user_id, $userid, $currency, $amount) {
        $crypto_settings = Session::get('crypto_settings', []);

        $settle_id = self::$_settle_info['new_settle_id'];

        // Add commission
        if (!isset(self::$_commissions[$user_id])) {
            self::$_commissions[$user_id] = array();
        }
        if (!isset(self::$_commissions[$user_id][$currency])) {
            self::$_commissions[$user_id][$currency] = array(
                'settle_id'     => $settle_id,
                'userid'        => $userid,
                'user_id'       => $user_id,
                'currency'      => $currency,
                'commission'    => $amount,
                'settle_status' => ENTRY_SETTLE_STATUS_NONE,
            );
        }
        else {
            $temp = Decimal::create(self::$_commissions[$user_id][$currency]['commission']);
            $temp = $temp->add(Decimal::create($amount));
            self::$_commissions[$user_id][$currency]['commission'] = $temp->__toString();
        }

        // Add balance
        $tbl = new TraderBalance();
        if (!isset(self::$_user_balances[$user_id])) {
            self::$_user_balances[$user_id] = array();
        }
        if (!isset(self::$_user_balances[$user_id][$currency])) {
            $prev_balance = $tbl->getUserBalance($user_id, $currency);

            self::$_user_balances[$user_id][$currency] = array(
                'userid'        => $userid,
                'prev_balance'  => $prev_balance,
                'next_balance'  => $prev_balance,
            );
        }
        $next_balance = Decimal::create(self::$_user_balances[$user_id][$currency]['next_balance']);
        $next_balance = $next_balance->add(Decimal::create($amount));
        self::$_user_balances[$user_id][$currency]['next_balance'] = $next_balance->__toString();

        // Add announce info
        if (!isset(self::$_announces[$user_id])) {
            self::$_announces[$user_id] = array(
                'userid'    => $userid,
                'user_id'   => $user_id,
            );
        }

        return true;
    }

    public function finishSettle($params) {
        // Load data from session
        self::$_settle_info = Session::get('affiliate_settle_info');
        self::$_user_balances = Session::get('affiliate_settle_user_balances');
        self::$_summaries = Session::get('affiliate_settle_summaries');
        self::$_commissions = Session::get('affiliate_settle_commissions');
        self::$_announces = Session::get('affiliate_settle_announces');

        $settle_id = self::$_settle_info['new_settle_id'];

        // Save settle
        $ret = self::where('id', $settle_id)
            ->update([
                'use_announce'  => $params['use_announce'],
                'remark'        => $params['remark'],
                'status'        => AFFILIATE_SETTLE_STATUS_FINISHED,
            ]);

        // Update settle status
        $ret = AffiliateSettleBalances::updateSettleStatus($settle_id, ENTRY_SETTLE_STATUS_FINISHED);
        $ret = AffiliateSettleCommission::updateSettleStatus($settle_id, ENTRY_SETTLE_STATUS_FINISHED);
        $ret = AffiliateSettleData::updateSettleStatus($settle_id, ENTRY_SETTLE_STATUS_FINISHED);

        // Save Commission
        $ret = AffiliateSettleSummary::insertRecords($settle_id, self::$_summaries);

        // Add balances
        $ret = TraderBalance::updateSettleBalance(self::$_user_balances);
        $ret = SystemBalance::decreaseSettleBalance($settle_id, SYSTEM_BALANCE_TYPE_AFFILIATE, self::$_summaries);

        if ($params['use_announce']) {
            $ret = AffiliateSettleMails::insertRecords($settle_id, self::$_announces);
        }

        return $ret;
    }
}
