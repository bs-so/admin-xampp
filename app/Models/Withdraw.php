<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Litipk\BigNumbers\Decimal;
use Auth;
use Log;
use Mockery\Exception;

class Withdraw extends Model
{
    protected $table            = 'olc_users_withdraw';
    protected $table_wallets    = 'olc_cold_wallets';
    protected $table_queue      = 'olc_users_withdraw_queue';
    protected $table_logs       = 'olc_realtime_logs';

    public static function create($data) {
        $new = new WithDraw();

        try {
            $new->user_id = $data['user_id'];
            $new->currency = $data['currency'];
            $new->amount = $data['amount'];
            $new->account_info = $data['account_info'];
            $new->status = $data['status'];

            $new->save();
        } catch(\Exception $e) {
            Log::debug("WithDraw:" . $e->getMessage());
            return null;
        }

        return $new;
    }

    public function getUserWithdrawData($currencies) {
        $result = array();
        $today = date('Y-m-d');

        // Init
        foreach ($currencies as $index => $currency) {
            $result[$currency->currency] = array(
                'last_updated'  => date('Y-m-d H:i:s'),
                'queue_count'   => 0,
                'queue_amount'  => 0,
                'today_count'   => 0,
                'today_amount'  => 0,
            );
        }

        // Queue Status
        $records = DB::table($this->table_queue)
            ->whereIn('status', [WITHDRAW_QUEUE_STATUS_REQUESTED, WITHDRAW_QUEUE_STATUS_PROCESSING])
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

        // Realtime Logs
        $records = DB::table($this->table_logs)
            ->where('type', LOGS_TYPE_USER_WITHDRAW)
            ->select('currency', 'last_updated')
            ->get();
        foreach ($records as $index => $record) {
            $currency = $record->currency;
            $result[$currency]['last_updated'] = $records[0]->last_updated;
        }

        // Today Result
        $records = DB::table($this->table)
            ->where('status', STATUS_ACCEPTED)
            ->where('updated_at', 'like', '%' . $today . '%')
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

    public static function updateState($id, $status) {
        $ret = self::where('id', $id)
            ->update([
                'status'    => $status,
            ]);

        return $ret;
    }

    public function updateStatus($id, $status) {
        $ret = DB::table($this->table)
            ->where('id', $id)
            ->update([
                'status'    => $status,
            ]);

        return $ret;
    }

    public static function getWithdrawTotalByUserID($user_id, $currency) {
        $records = self::where('user_id', $user_id)
            ->where('currency', $currency)
            ->selectRaw('sum(amount) as total')
            ->get();

        if (!isset($records) || count($records) == 0 || $records[0]->total == '') {
            return 0;
        }

        return $records[0]->total;
    }

    public static function updateRecords($where, $data) {
        $ret = self::where($where)
            ->update($data);

        return $ret;
    }

    public static function getHistoryByWhere($where) {
        $history = Withdraw::where($where)->get();
        if (empty($history)) {
            return [];
        }
        return $history->toArray();
    }

    public static function getSumByCurrency() {
        try {
            $total_sum = DB::table('users_withdraw')
                    ->select(DB::raw('currency'), DB::raw('sum(amount) as total'))
                    ->groupBy(DB::raw('currency'))
                    ->get()
                    ->toArray();
            return $total_sum;
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function updateStateByWhere($where, $data) {
        $ret = Withdraw::where($where)->first();

        try {
            $ret->status = $data['status'];
            $ret->save();
        } catch(\Exception $e) {
            return false;
        }

        return true;
    }

    public function getWalletBalances($currency) {
        $records = DB::table($this->table_wallets)
            ->where('currency', $currency)
            ->where('type', COLD_WALLET_WITHDRAW)
            ->where('specified', SPECIFIED_YES)
            ->select('*')
            ->get();

        if (!isset($records) || count($records) == 0) {
            return 1;
        }

        $result = array(
            $currency   => 0,
            'ETH'       => 0,
        );

        $tbl = new ColdWallet();
        foreach ($records as $index => $record) {
            $wallet_address = $record->wallet_address;
            $ret = $tbl->getBalance([
                'currency'          => $currency,
                'wallet_address'    => $wallet_address,
            ]);

            if (!isset($ret['balance'])) {
                return 2;
            }

            $result[$currency] = $ret['balance'];
        }

        // If token, then get gastank balance
        if ($currency == MAIN_CURRENCY || $currency == 'USDT') {
            $records = DB::table($this->table_wallets)
                ->where('currency', 'ETH')
                ->where('type', COLD_WALLET_GASTANK)
                ->where('specified', SPECIFIED_YES)
                ->select('*')
                ->get();

            if (!isset($records) || count($records) == 0) {
                return 3;
            }

            foreach ($records as $index => $record) {
                $wallet_address = $record->wallet_address;
                $ret = $tbl->getBalance([
                    'currency' => 'ETH',
                    'wallet_address' => $wallet_address,
                ]);

                if (!isset($ret['balance'])) {
                    return 4;
                }

                $result['ETH'] = $ret['balance'];
            }
        }

        return $result;
    }

    /**
     * Fetch cash withdraw data according to DataTables-request,
     * and return result as DataTables-response style
     * 2018/11/30 Created by H(S
     *
     * @param $params
     * @return mixed
     */
    public function getCashForDatatable($params) {
        $selector = DB::table($this->table)
            ->leftJoin('team', $this->table . '.team_id', '=', 'team.id')
            ->whereIn('currency', ['UAE', 'USD']);

        // filtering
        if (isset($params['columns'][1]['search']['value'])
            && $params['columns'][1]['search']['value'] !== '' && $params['columns'][1]['search']['value'] !== '0'
        ) {
            $selector->where($this->table . '.team_id', $params['columns'][1]['search']['value']);
        }
        else if (isset($params['team_id']) && $params['team_id'] !== '' && $params['team_id'] !== '0') {
            $selector->where($this->table . '.team_id', '=', $params['team_id']);
        }
        $totalCount = $selector->count();

        if (isset($params['columns'][2]['search']['value'])
            && $params['columns'][2]['search']['value'] !== ''
        ) {
            $traderTbl = new Trader();
            $traderId = $traderTbl->getTraderIdByName($params['columns'][2]['search']['value']);
            if ($traderId > 0) $selector->where('user_id', $traderId);
        }
        if (isset($params['columns'][3]['search']['value'])
            && $params['columns'][3]['search']['value'] !== ''
        ) {
            $selector->whereRaw('country_code like ' . "'%" . $params['columns'][3]['search']['value'] . "%'");
        }
        if (isset($params['columns'][5]['search']['value'])
            && $params['columns'][5]['search']['value'] !== ''
        ) {
            $amountRange = preg_replace('/[\$\,]/', '', $params['columns'][5]['search']['value']);
            $elements = explode(':', $amountRange);

            if ($elements[0] != "" || $elements[1] != "")
                $selector->whereBetween('amount', $elements);
        }
        if (isset($params['columns'][7]['search']['value'])
            && $params['columns'][7]['search']['value'] !== ''
        ) {
            $selector->where($this->table . '.status', $params['columns'][7]['search']['value']);
        }
        else if (isset($params['status']) && $params['status'] !== '') {
            $selector->where($this->table . '.status', $params['status']);
        }
        if (isset($params['columns'][8]['search']['value'])
            && $params['columns'][8]['search']['value'] !== ''
        ) {
            $amountRange = preg_replace('/[\$\,]/', '', $params['columns'][8]['search']['value']);
            $elements = explode(':', $amountRange);

            if ($elements[0] != "" || $elements[1] != "") {
                $elements[0] .= ' 00:00:00';
                $elements[1] .= ' 23:59:59';
                $selector->whereBetween($this->table . '.created_at', $elements);
            }
        }

        $selector->select($this->table . '.*', 
            'team.name as team_name');

        // number of filtered records
        $recordsFiltered = $selector->count();

        // sort
        $traderOrderDir = '';
        foreach ($params['order'] as $order) {
            $field = $params['columns'][$order['column']]['data'];
            if ($field == 'user_id') $traderOrderDir = $order['dir'];
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

        // get traders data
        $ids = array();
        $recordCount = count($records);
        for ($i = 0; $i < $recordCount; $i ++){
            $ids[$i] = $records[$i]->user_id;
        }

        $traderTbl = new Trader();
        $tradersData = $traderTbl->getTraderData($ids, $traderOrderDir);

        for ($i = 0; $i < $recordCount; $i ++){
            if (!isset($tradersData[$ids[$i]])) {
                $records[$i]->login_id = '';
                continue;
            }

            $records[$i]->login_id = $tradersData[$ids[$i]]->login_id;
        }

        return [
            'draw' => $params['draw']+0,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }

    /**
     * Confirm statuses
     * 2018/12/19 Created by H(S
     *
     * @param $ids
     * @return result
     */
    public function confirmCashStatus($ids) {
        $selector = DB::table($this->table)
            ->whereIn('id', $ids)
            ->update(['status' => TRANSFER_STATUS_CONFIRMED]);

       return 1;
    }

    /**
     * Get records by id array
     * 2018/12/20 Created by H(S
     *
     * @param $ids
     * @return result
     */
    public function getCashRecordsByIds($ids) {
         $selector = DB::table($this->table)
            ->whereIn('id', $ids)
            ->select('*');

        return $selector->get();
    }

    /**
     * Fetch crypto withdraw data according to DataTables-request,
     * and return result as DataTables-response style
     * 2018/11/30 Created by H(S
     *
     * @param $params
     * @return mixed
     */
    public function getCryptoForDatatable($params) {
        $selector = DB::table($this->table)
            ->whereNotIn('currency', ['JPY'])
            ->where('status', '!=', WITHDRAW_STATUS_REQUEST);

        // filtering
        $totalCount = $selector->count();

        if (isset($params['columns'][1]['search']['value'])
            && $params['columns'][1]['search']['value'] !== ''
        ) {
            $traderTbl = new Trader();
            $ids = $traderTbl->getTraderIdsByName($params['columns'][1]['search']['value']);
            if ($ids !== false) $selector->whereIn('user_id', $ids);
        }
        if (isset($params['columns'][2]['search']['value'])
            && $params['columns'][2]['search']['value'] !== ''
        ) {
            $selector->whereRaw('country_code like ' . "'%" . $params['columns'][2]['search']['value'] . "%'");
        }
        if (isset($params['columns'][3]['search']['value'])
            && $params['columns'][3]['search']['value'] !== '' && $params['columns'][3]['search']['value'] !== '99'
        ) {
            $selector->where($this->table . '.currency', $params['columns'][3]['search']['value']);
        }
        if (isset($params['columns'][4]['search']['value'])
            && $params['columns'][4]['search']['value'] !== ''
        ) {
            $selector->where($this->table . '.account_info', 'like', '%' . $params['columns'][4]['search']['value'] . '%');
        }
        if (isset($params['columns'][8]['search']['value'])
            && $params['columns'][8]['search']['value'] !== '' && $params['columns'][8]['search']['value'] !== '99'
        ) {
            $selector->where($this->table . '.status', $params['columns'][8]['search']['value']);
        }
        if (isset($params['columns'][9]['search']['value'])
            && $params['columns'][9]['search']['value'] !== ''
        ) {
            $amountRange = preg_replace('/[\$\,]/', '', $params['columns'][9]['search']['value']);
            $elements = explode(':', $amountRange);

            if ($elements[0] != "" || $elements[1] != "") {
                $elements[0] .= ' 00:00:00';
                $elements[1] .= ' 23:59:59';
                $selector->whereBetween($this->table . '.updated_at', $elements);
            }
        }

        $selector->select(
            $this->table . '.*'
        );

        // number of filtered records
        $recordsFiltered = $selector->count();

        // sort
        $traderOrderDir = '';
        foreach ($params['order'] as $order) {
            $field = $params['columns'][$order['column']]['data'];
            if ($field == 'user_id') $traderOrderDir = $order['dir'];
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

        // get traders data
        $ids = array();
        $recordCount = count($records);
        for ($i = 0; $i < $recordCount; $i ++){
            $ids[$i] = $records[$i]->user_id;
        }

        $traderTbl = new Trader();
        $tradersData = $traderTbl->getTraderData($ids, $traderOrderDir);

        for ($i = 0; $i < $recordCount; $i ++){
            if (!isset($tradersData[$ids[$i]])) {
                $records[$i]->email = '';
                continue;
            }

            $records[$i]->trader_id = $ids[$i];
            $records[$i]->email = $tradersData[$ids[$i]]->email;
        }

        return [
            'draw' => $params['draw']+0,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }

    public function getWithdrawOutLine($params) {
        $selector = DB::table($this->table)
            ->whereNotIn('currency', [JPY_CURRENCY])
            ->groupBy('currency');

        if (isset($params['team_id']) && $params['team_id'] !== '' && $params['team_id'] !== '0') {
            $selector->where('team_id', '=', $params['team_id']);
        }

        $selector->where('status', WITHDRAW_STATUS_REQUEST);
		$selector->where('account_info', 'not like', '%MLM%');

        $selector->select($this->table . '.currency',
            DB::raw('count(currency) as withdraw_count'),
            DB::raw('sum(amount) as withdraw_sum')
        );
        // number of filtered records
        $recordCount = $selector->count();
        $count = $recordCount;
        // get records
        $recordsWithDraw = $selector->get();

        $selector = DB::table($this->table)
            ->whereNotIn('currency', [JPY_CURRENCY])
            ->groupBy('currency');
        if (isset($params['team_id']) && $params['team_id'] !== '' && $params['team_id'] !== '0') {
            $selector->where('team_id', '=', $params['team_id']);
        }
        $selector->where('status', WITHDRAW_STATUS_PROCESSING);
        $selector->select($this->table . '.currency',
            DB::raw('count(currency) as processing_count'),
            DB::raw('sum(amount) as processing_sum')
        );
        // number of filtered records
        $recordCount = $selector->count();
        // get records
        $recordsProcessing = $selector->get();

        $selector = DB::table($this->table)
            ->whereNotIn('currency', [JPY_CURRENCY])
            ->groupBy('currency');
        if (isset($params['team_id']) && $params['team_id'] !== '' && $params['team_id'] !== '0') {
            $selector->where('team_id', '=', $params['team_id']);
        }
        $selector->where('status', WITHDRAW_STATUS_FAILED);
        $selector->select($this->table . '.currency',
            DB::raw('count(currency) as failed_count'),
            DB::raw('sum(amount) as failed_sum')
        );
        // number of filtered records
        $recordCount = $selector->count();
        // get records
        $recordsFailed = $selector->get();

        // var_dump($recordsWithDraw);
        // var_dump($recordsProcessing);
        // var_dump($recordsFailed);

        $newArr = [];
        foreach ($recordsWithDraw as $index => $withraw) {
            $newArr[$index] = $recordsWithDraw[$index];
            $newArr[$index]->processing_count = 0;
            $newArr[$index]->processing_sum = 0;
            $newArr[$index]->failed_count = 0;
            $newArr[$index]->failed_sum = 0;
            
            foreach ($recordsProcessing as $pindex => $processing) {
                if ($withraw->currency == $processing->currency) {
                    $newArr[$index]->processing_count   = isset($recordsProcessing[$pindex]->processing_count)? $recordsProcessing[$pindex]->processing_count : 0;
                    $newArr[$index]->processing_sum     = isset($recordsProcessing[$pindex]->processing_sum)? $recordsProcessing[$pindex]->processing_sum : 0;
                    break;
                }
            }
            
            foreach ($recordsFailed as $findex => $failed) {
                if ($withraw->currency == $failed->currency) {
                    $newArr[$index]->failed_count       = isset($recordsFailed[$findex]->failed_count)? $recordsFailed[$findex]->failed_count : 0;
                    $newArr[$index]->failed_sum         = isset($recordsFailed[$findex]->failed_sum)? $recordsFailed[$findex]->failed_sum : 0;
                    break;
                }
            }
        }
        
        $newRecords = collect(
           $newArr
        );

        return [
            'draw' => $params['draw']+0,
            'recordsTotal' => $count,
            'recordsFiltered' => $count,
            'data' => $newRecords,
            'error' => 0,
        ];
    }

    public function getWithdrawList($params) {
        $selector = DB::table($this->table)
            ->leftJoin('cryptocurrencies', 'cryptocurrencies.currency', '=', $this->table.'.currency')
			->where($this->table . '.account_info', 'not like', '%MLM%')
            ->where($this->table.'.status', '=', WITHDRAW_STATUS_REQUEST);

        // filtering
        if (isset($params['curr_id']) && $params['curr_id'] !== '' && $params['curr_id'] !== '0') {
            $selector->where($this->table.'.currency', '=', $params['curr_id']);
        }
        $totalCount = $selector->count();

        if (isset($params['columns'][2]['search']['value'])
            && $params['columns'][2]['search']['value'] !== ''
        ) {
            $traderTbl = new Trader();
            $ids = $traderTbl->getTraderIdsByName($params['columns'][2]['search']['value']);
            if ($ids !== false) $selector->whereIn('user_id', $ids);
        }
        if (isset($params['columns'][4]['search']['value'])
            && $params['columns'][4]['search']['value'] !== ''
        ) {
            $selector->where('account_info', 'like', '%' . $params['columns'][4]['search']['value'] . '%');
        }
        if (isset($params['columns'][6]['search']['value'])
            && $params['columns'][6]['search']['value'] !== ''
        ) {
            $dateRange = preg_replace('/[\$\,]/', '', $params['columns'][6]['search']['value']);
            $elements = explode(':', $dateRange);

            if ($elements[0] != "" || $elements[1] != "") {
                $elements[0] .= ' 00:00:00';
                $elements[1] .= ' 23:59:59';
                $selector->whereBetween($this->table . '.created_at', $elements);
            }
        }

        $selector->select($this->table . '.*');
		$filtered = $selector->count();

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

        // Get user info
        $tbl = new Trader();
        foreach ($records as $index => $record) {
            $userInfo = $tbl->getTraderInfo($record->user_id);
            if ($userInfo === false) {
                $record->email = '';
                $record->name = '';
            }
            else {
                $record->email = $userInfo->email;
                $record->name = $userInfo->name;
                $record->trader_id = $userInfo->id;
            }
        }

        return [
            'draw' => $params['draw']+0,
            'recordsFiltered' => $filtered,
            'recordsTotal' => $totalCount,
            'data' => $records,
            'error' => 0,
        ];
    }

    public function addWithdrawQueue($params) {
        try {
            $user = Auth::user();
            $coldwallets = DB::table($this->table_wallets)
                ->where('currency', $params['currency'])
                ->where('type', COLD_WALLET_WITHDRAW)
                ->where('specified', SPECIFIED_YES)
                ->get();

            if (!isset($coldwallets) || empty($coldwallets)) {
                // No withdraw wallet
                return 2;
            }

            DB::beginTransaction();

            $walletBalance = Decimal::create($coldwallets[0]->wallet_balance);
            foreach ($params['selected'] as $id) {
                $records = DB::table($this->table)
                    ->where('id', $id)
                    ->get();

                if (!isset($records) || empty($records)) {
                    continue;
                }
                if ($walletBalance->isNegative()) {
                    // No more balance
                    break;
                }
                $walletBalance->sub(Decimal::create($records[0]->amount));

                $ret = DB::table($this->table_queue)
                    ->insert([
                        'withdraw_id' => $records[0]->id,
                        'currency' => $records[0]->currency,
                        'user_id' => $records[0]->user_id,
                        'country_code' => $records[0]->country_code,
                        'team_id' => $records[0]->team_id,
                        'cold_wallet_id' => $coldwallets[0]->id,
                        'user_wallet_address' => $records[0]->account_info,
                        'amount' => $records[0]->amount,
                        'status' => WITHDRAW_QUEUE_STATUS_REQUESTED,
                        'note' => '',
                        'created_by' => $user->id,
                    ]);

                $ret = DB::table($this->table)
                    ->where('id', $id)
                    ->update([
                        'status'    => WITHDRAW_STATUS_PROCESSING,
                    ]);
            }

            DB::commit();
            return 0;
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::channel('daily')->error("Add withdraw queue has failed. Error: " . $e->getMessage());
            return 1;
        }
    }

    public function cancelRequests($params) {
        foreach ($params['selected'] as $id) {
            $ret = DB::table($this->table)
                ->where('id', $id)
                ->update([
                    'status'    => WITHDRAW_STATUS_CANCELLED,
                    'remark'    => $params['reason'],
                ]);
        }

        return 0;
    }

    public function getWithdrawUserId($id) {
        $records = DB::table($this->table)
            ->where('id', $id)
            ->select('user_id')
            ->get();

        if (!isset($records) || count($records) == 0) {
            return 0;
        }

        return $records[0]->user_id;
    }

    public function getRecordByID($id) {
        $records = DB::table($this->table)
            ->where('id', $id)
            ->select('*')
            ->get();

        if (!isset($records) || count($records) == 0) {
            return [];
        }

        return $records[0];
    }

    /**
     * Get count of requested records
     * 2019/01/09 Created by H(S
     *
     * @param $ids
     * @return result
     */
    public function getRequestedCount($type, $prevTimestamp = 0, $nowTimestamp = 0) {
        $prevTime = date('Y-m-d H:i:s', $prevTimestamp);
        $nowTime = date('Y-m-d H:i:s', $nowTimestamp);

        $selector = DB::table($this->table)
            ->where('status', TRANSFER_STATUS_REQUESTED);

        if ($type == 1) { // Cash
            $selector->whereIn('currency', ['UAE', 'USD']);
        }
        else if ($type == 2) { // Crypto
            $selector->whereNotIn('currency', ['UAE', 'USD']);
        }

        if ($prevTimestamp != '') {
            $selector->whereBetween('created_at', [$prevTime, $nowTime]);
        }

        $selector->selectRaw('count(*) as count');

        return $selector->get();
    }
}
