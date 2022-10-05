<?php

namespace App\Models;

use DB;
use Auth;
use Log;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Litipk\BigNumbers\Decimal;

class TraderWithdraw extends Authenticatable
{
    use Notifiable;
    protected $table = 'olc_users_withdraw';
    protected $table_user = 'olc_users';
    protected $table_wallets = 'olc_cold_wallets';
    protected $table_queue = 'olc_users_withdraw_queue';

    public function makeCsv($filename, $trader_id = 0) {
        $csv = '';
        $titles = ['no', 'user_name', 'currency', 'destination', 'amount', 'withdraw_fee', 'transfer_fee', 'gas_price', 'gas_used', 'tx_id', 'status', 'remark', 'reged_at'];
        foreach ($titles as $index => $title) {
            $csv .= trans('users-history.withdraw.' . $title) . ',';
        }
        $csv = substr($csv, 0, strlen($csv) - 1) . "\n";

        $selector = DB::table($this->table)
            ->leftJoin($this->table_user, $this->table_user . '.id', '=', $this->table . '.user_id')
            ->where($this->table . '.status', '!=', STATUS_REQUESTED)
            ->select(
                $this->table . '.*',
                $this->table_user . '.nickname as user_name'
            );
        if ($trader_id > 0) {
            $selector->where('user_id', $trader_id);
        }
        $records = $selector->get();
        foreach ($records as $index => $record) {
            $csv .= ($index + 1) . ',';
            $csv .= $record->user_name . ',';
            $csv .= $record->currency . ',';
            $csv .= $record->destination . ',';
            $csv .= $record->amount . ',';
            $csv .= $record->withdraw_fee . ',';
            $csv .= $record->transfer_fee . ',';
            $csv .= $record->gas_price . ',';
            $csv .= $record->gas_used . ',';
            $csv .= $record->tx_id . ',';
            $csv .= g_enum('UsersWithdrawStatus')[$record->status][0] . ',';
            $csv .= $record->remark . ',';
            $csv .= $record->created_at . "\n";
        }

        file_put_contents($filename, "\xEF\xBB\xBF". $csv);
    }

	public static function getProcessingCount($user_id) {
		$ret = self::where('user_id', $user_id)
				->whereIn('status', [STATUS_PENDING])
				->count();

		return $ret;
	}

    public static function getAll() {
        $records = self::where('status', STATUS_ACTIVE)
            ->get();

        if (!isset($records) || count($records) == 0) {
            return [];
        }

        return $records;
    }

    public static function updateRecords($where, $data) {
        $ret = self::where($where)
            ->update($data);

        return $ret;
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

    public function getWithdrawOutLine($params) {
        $selector = DB::table($this->table)
            ->whereNotIn('currency', [JPY_CURRENCY])
            ->groupBy('currency');

        $selector->where('status', STATUS_REQUESTED);
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

        $selector->where('status', STATUS_PENDING);
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

        $selector->where('status', STATUS_FAILED);
        $selector->select($this->table . '.currency',
            DB::raw('count(currency) as failed_count'),
            DB::raw('sum(amount) as failed_sum')
        );
        // number of filtered records
        $recordCount = $selector->count();
        // get records
        $recordsFailed = $selector->get();

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

    public function getWalletBalances($currency) {
        $records = DB::table($this->table_wallets)
            ->where('currency', $currency)
            ->where('type', WALLET_TYPE_WITHDRAW)
            ->where('specified', WALLET_SPECIFIED_WITHDRAW)
            ->select('*')
            ->get();

        if (!isset($records) || count($records) == 0) {
            return 1;
        }

        $result = array(
            $currency   => 0,
            'ETH'       => 0,
        );

        $tbl = new ColdWallets();
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
        if ($currency == 'USDT') {
            $records = DB::table($this->table_wallets)
                ->where('currency_code', 'ETH')
                ->where('type', WALLET_TYPE_GASTANK)
                ->where('specified', WALLET_SPECIFIED_GASTANK)
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

    public function getWithdrawList($params) {
        $selector = DB::table($this->table)
            ->leftJoin($this->table_user, $this->table_user . '.id', '=', $this->table . '.user_id')
            ->where($this->table.'.status', '=', STATUS_REQUESTED)
            ->select(
                $this->table . '.*',
                $this->table_user . '.userid as userid',
                $this->table_user . '.nickname as nickname',
                $this->table_user . '.email as email'
            );

        // filtering
        if (isset($params['currency']) && $params['currency'] !== '' && $params['currency'] !== '0') {
            $selector->where($this->table.'.currency', '=', $params['currency']);
        }
        $totalCount = $selector->count();

        if (isset($params['columns'][1]['search']['value'])
            && $params['columns'][1]['search']['value'] !== ''
        ) {
            $selector->where($this->table_user . '.userid', 'like', '%' . $params['columns'][1]['search']['value'] . '%');
        }
        if (isset($params['columns'][3]['search']['value'])
            && $params['columns'][3]['search']['value'] !== ''
        ) {
            $selector->where($this->table_user . '.email', 'like', '%' . $params['columns'][3]['search']['value'] . '%');
        }
        if (isset($params['columns'][5]['search']['value'])
            && $params['columns'][5]['search']['value'] !== ''
        ) {
            $selector->where('destination', 'like', '%' . $params['columns'][5]['search']['value'] . '%');
        }
        if (isset($params['columns'][7]['search']['value'])
            && $params['columns'][7]['search']['value'] !== ''
        ) {
            $dateRange = preg_replace('/[\$\,]/', '', $params['columns'][7]['search']['value']);
            $elements = explode(':', $dateRange);

            if ($elements[0] != "" || $elements[1] != "") {
                $elements[0] .= ' 00:00:00';
                $elements[1] .= ' 23:59:59';
                $selector->whereBetween($this->table . '.created_at', $elements);
            }
        }

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
                ->where('type', WALLET_TYPE_WITHDRAW)
                ->where('specified', WALLET_SPECIFIED_WITHDRAW)
                ->get();

            if (!isset($coldwallets) || empty($coldwallets)) {
                // No withdraw wallet
                return 2;
            }

            DB::beginTransaction();

            $walletBalance = Decimal::create($coldwallets[0]->balance);
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
                        'cold_wallet_id' => $coldwallets[0]->id,
                        'to_address' => $records[0]->destination,
                        'amount' => $records[0]->amount,
                        'status' => WITHDRAW_QUEUE_STATUS_REQUESTED,
                        'remark' => '',
                    ]);

                $ret = DB::table($this->table)
                    ->where('id', $id)
                    ->update([
                        'status'    => STATUS_PENDING,
                    ]);
            }

            DB::commit();
            return 0;
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error("Add withdraw queue has failed. Error: " . $e->getMessage());
            return 1;
        }
    }

    public function cancelRequests($params) {
        foreach ($params['selected'] as $id) {
            $ret = DB::table($this->table)
                ->where('id', $id)
                ->update([
                    'status'    => STATUS_CANCELLED,
                    'remark'    => $params['remark'],
                ]);
        }

        return 0;
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->leftJoin($this->table_user, $this->table_user . '.id', '=', $this->table . '.user_id')
            ->where($this->table . '.status', '!=', STATUS_REQUESTED)
            ->select(
                $this->table . '.*',
                $this->table_user . '.userid as userid',
                $this->table_user . '.nickname as user_name'
            );

        if (isset($params['user_id'])) {
            $selector->where('user_id', $params['user_id']);
        }
        $recordsTotal = $selector->count();

        // filtering
        if (isset($params['columns'][1]['search']['value'])
            && $params['columns'][1]['search']['value'] !== ''
        ) {
            $selector->where($this->table_user . '.userid', 'like', '%' . $params['columns'][1]['search']['value'] . '%');
        }
        if (isset($params['columns'][2]['search']['value'])
            && $params['columns'][2]['search']['value'] !== ''
        ) {
            $selector->where($this->table_user . '.nickname', 'like', '%' . $params['columns'][2]['search']['value'] . '%');
        }
        if (isset($params['columns'][3]['search']['value'])
            && $params['columns'][3]['search']['value'] !== ''
        ) {
            $selector->where('currency', 'like', '%' . $params['columns'][3]['search']['value'] . '%');
        }
        if (isset($params['columns'][4]['search']['value'])
            && $params['columns'][4]['search']['value'] !== ''
        ) {
            $selector->where('type', $params['columns'][4]['search']['value']);
        }
        if (isset($params['columns'][5]['search']['value'])
            && $params['columns'][5]['search']['value'] !== ''
        ) {
            $selector->where('destination', 'like', '%' . $params['columns'][5]['search']['value'] . '%');
        }
        if (isset($params['columns'][6]['search']['value'])
            && $params['columns'][6]['search']['value'] !== ''
        ) {
            $selector->where('amount', $params['columns'][6]['search']['value']);
        }
        if (isset($params['columns'][7]['search']['value'])
            && $params['columns'][7]['search']['value'] !== ''
        ) {
            $selector->where('tx_id', 'like', '%' . $params['columns'][7]['search']['value'] . '%');
        }
        if (isset($params['columns'][8]['search']['value'])
            && $params['columns'][8]['search']['value'] !== ''
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

        return [
            'draw' => $params['draw']+0,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }
}
