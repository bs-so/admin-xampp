<?php

namespace App\Models;

use DB;
use Log;
use Auth;
use Litipk\BigNumbers\Decimal;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class SystemBalance extends Model
{
    protected $table = 'olc_system_balances';
    protected $table_users_balance = 'olc_users_balance';
    protected $table_profit = 'olc_system_profits';

    public function getAll($type) {
        $records = self::where('type', $type)
            ->pluck('balance', 'currency');

        return $records;
    }

    public function getAllRecords($types) {
        $records = self::whereIn('type', $types)
            ->orderBy('currency', 'asc')
            ->select('*')
            ->get();

        return $records;
    }

    public function pluckRecords($type) {
        $records = self::where('type', $type)
            ->orderBy('currency', 'asc')
            ->pluck('balance', 'currency');

        return $records;
    }

    public function getBalance($type, $currency) {
        $record = self::where('type', $type)
            ->where('currency', $currency)
            ->select('balance')
            ->first();

        if (!isset($record) || !isset($record->balance)) {
            return 0;
        }

        return $record->balance;
    }

    public static function decreaseSettleBalance($settle_id, $type, $records) {
        try {
            DB::beginTransaction();

            foreach ($records as $currency => $data) {
                $amount = $data['total_commission'];
                $record = self::where('type', $type)
                    ->where('currency', $currency)
                    ->select('id', 'balance')
                    ->lockForUpdate()
                    ->first();

                $balance = Decimal::create($record->balance == '' ? 0 : $record->balance);
                $balance = $balance->sub(Decimal::create($data['total_commission']));
                $ret = self::where('id', $record->id)
                    ->update([
                        'balance'   => $balance->__toString(),
                    ]);
            }

            DB::commit();
            return true;
        }
        catch (\Exception $ex) {
            DB::rollback();
            Log::error($ex->getMessage());
            return false;
        }
    }

    public function addProfit($user_id, $type, $currency, $profit) {
        try {
            DB::beginTransaction();

            $records = DB::table($this->table)
                ->where('type', $type)
                ->where('currency', $currency)
                ->select('id', 'balance')
                ->lockForUpdate()
                ->get();
            if (!isset($records) || count($records) == 0) {
                // Insert
                $ret = DB::table($this->table)
                    ->insert([
                        'type'      => $type,
                        'currency'  => $currency,
                        'balance'   => $profit,
                    ]);
            }
            else {
                // Add
                $balance = (isset($records[0]->balance) && $records[0]->balance != '') ? $records[0]->balance : 0;
                $balance = Decimal::create($balance);
                $balance = $balance->add(Decimal::create($profit));
                $ret = DB::table($this->table)
                    ->where('id', $records[0]->id)
                    ->update([
                        'balance'   => $balance->__toString(),
                    ]);
            }

            $records = DB::table($this->table_profit)
                ->where('date', date('Y-m-d'))
                ->where('type', $type)
                ->where('user_id', $user_id)
                ->where('currency', $currency)
                ->select('id', 'profit')
                ->lockForUpdate()
                ->get();
            if (!isset($records) || count($records) == 0) {
                $ret = DB::table($this->table_profit)
                    ->insert([
                        'date'      => date('Y-m-d'),
                        'type'      => $type,
                        'user_id'   => $user_id,
                        'currency'  => $currency,
                        'profit'    => $profit,
                    ]);
            }
            else {
                $_profit = (isset($records[0]->profit) && $records[0]->profit != '') ? $records[0]->profit : 0;
                $_profit = Decimal::create($_profit);
                $_profit = $_profit->add(Decimal::create($profit));
                $ret = DB::table($this->table_profit)
                    ->where('id', $records[0]->id)
                    ->update([
                        'profit'    => $_profit->__toString(),
                    ]);
            }

            DB::commit();

            return true;
        }
        catch (\Exception $ex) {
            DB::rollback();
            Log::error($ex->getMessage());
        }
    }

    public function insertCasinoLog($user_id, $type, $currency, $profit) {
        try {
            DB::beginTransaction();

            $records = DB::table($this->table)
                ->where('type', $type)
                ->where('currency', $currency)
                ->select('id', 'balance')
                ->lockForUpdate()
                ->get();
            if (!isset($records) || count($records) == 0) {
                // Insert
                $ret = DB::table($this->table)
                    ->insert([
                        'type'      => $type,
                        'currency'  => $currency,
                        'balance'   => $profit,
                    ]);
            }
            else {
                // Add
                $balance = (isset($records[0]->balance) && $records[0]->balance != '') ? $records[0]->balance : 0;
                $balance = Decimal::create($balance);
                $balance = $balance->add(Decimal::create($profit));
                $ret = DB::table($this->table)
                    ->where('id', $records[0]->id)
                    ->update([
                        'balance'   => $balance->__toString(),
                    ]);
            }

            $ret = DB::table($this->table_profit)
                ->insert([
                    'date'      => date('Y-m-d'),
                    'type'      => SYSTEM_PROFIT_TYPE_CASINO,
                    'user_id'   => $user_id,
                    'currency'  => $currency,
                    'profit'    => $profit,
                ]);

            DB::commit();

            return true;
        }
        catch (\Exception $ex) {
            DB::rollback();
            Log::channel('api')->error($ex->getMessage());
            return false;
        }
    }

    public function setCasinoBalance($params) {
        try {
            DB::beginTransaction();

            $records = self::where('type', SYSTEM_BALANCE_TYPE_CASINO_MANUAL)
                ->where('currency', $params['currency'])
                ->select('id', 'balance')
                ->lockForUpdate()
                ->get();

            // Add casino manual balance
            $amount = Decimal::create($params['amount'] == '' ? 0 : $params['amount']);
            if (!isset($records) || count($records) == 0) {
                $ret = self::insert([
                    'type'      => SYSTEM_BALANCE_TYPE_CASINO_MANUAL,
                    'currency'  => $params['currency'],
                    'balance'   => $params['amount'],
                ]);
            }
            else {
                $balance = Decimal::create($records[0]->balance);
                $balance = $balance->add($amount);
                $ret = self::where('id', $records[0]->id)
                    ->update([
                        'balance'   => $balance->__toString(),
                    ]);
            }

            // Minus casino auto balance
            $records = self::where('type', SYSTEM_BALANCE_TYPE_CASINO_AUTO)
                ->where('currency', $params['currency'])
                ->select('id', 'balance')
                ->lockForUpdate()
                ->get();
            if (!isset($records) || count($records) == 0) {
                $ret = self::insert([
                    'type'      => SYSTEM_BALANCE_TYPE_CASINO_AUTO,
                    'currency'  => $params['currency'],
                    'balance'   => -$params['amount'],
                ]);
            }
            else {
                $balance = Decimal::create($records[0]->balance);
                $balance = $balance->sub($amount);
                $ret = self::where('id', $records[0]->id)
                    ->update([
                        'balance'   => $balance->__toString(),
                    ]);
            }

            $ret = SystemTransfer::insertRecord(array(
                'staff_id'      => Auth::user()->id,
                'type'          => SYSTEM_BALANCE_TYPE_CASINO_MANUAL,
                'direction'     => TRANSFER_DIRECTION_SET,
                'currency'      => $params['currency'],
                'amount'        => $params['amount'],
                'remark'        => $params['remark'],
                'status'        => STATUS_ACTIVE,
            ));

            DB::commit();
            return $ret;
        }
        catch (\Exception $ex) {
            DB::rollback();
            Log::error($ex->getMessage());
            return false;
        }
    }

    public function transferAffiliateBalance($params) {
        try {
            // 1. Get casino balance
            $records = self::where('type', SYSTEM_BALANCE_TYPE_CASINO_MANUAL)
                ->where('currency', $params['currency'])
                ->select('id', 'balance')
                ->get();

            $casino_balance = Decimal::create(0);
            if (isset($records) && count($records) > 0 && $records[0]->balance != '') {
                $casino_balance = Decimal::create($records[0]->balance);
            }

            $amount = Decimal::create($params['amount'] == '' ? 0 : $params['amount']);
            if ($casino_balance->isLessThan($amount)) {
                // Not enough balance
                return 1;
            }

            // 2. Do Transfer
            DB::beginTransaction();

            // 2.0 Get Transfer fee
            $fee_percent = Master::getValue(TRANSFER_FEE);
            $fee_percent = Decimal::create($fee_percent);

            // 2.1 Decrease casino balance
            $casino_balance = $casino_balance->sub($amount);
            $ret = self::where('id', $records[0]->id)
                ->update([
                    'balance'       => $casino_balance->__toString(),
                ]);

            // 2.2 Calulcate fee & amount
            $fee = Decimal::create($amount);
            $fee = $fee->mul($fee_percent);
            $fee = $fee->div(Decimal::create(100));

            $amount = $amount->sub($fee);

            // 2.3 Increase affiliate balance
            $user_id = Trader::getIDByUserID($params['userid']);
            $records = DB::table($this->table_users_balance)
                ->where('user_id', $user_id)
                ->where('currency', $params['currency'])
                ->select('id', 'balance')
                ->lockForUpdate()
                ->get();

            $ret = true;
            if (isset($records) && count($records) > 0) {
                // Update
                $balance = Decimal::create($records[0]->balance);
                $balance = $balance->add($amount);
                $ret = DB::table($this->table_users_balance)
                    ->where('id', $records[0]->id)
                    ->update([
                        'balance'   => $balance->__toString(),
                    ]);
            }
            else {
                $ret = DB::table($this->table_users_balance)
                    ->insert([
                        'user_id'   => $user_id,
                        'currency'  => $params['currency'],
                        'balance'   => $amount->__toString(),
                    ]);
            }

            // 2.4 Add system transfer history
            $ret = SystemTransfer::insertRecord(array(
                'staff_id'      => Auth::user()->id,
                'user_id'       => $user_id,
                'type'          => SYSTEM_BALANCE_TYPE_AFFILIATE,
                'direction'     => TRANSFER_DIRECTION_IN,
                'currency'      => $params['currency'],
                'amount'        => $params['amount'],
                'remark'        => $params['remark'],
                'status'        => STATUS_ACTIVE,
            ));

            // 2.5 Add profit
            $profitTbl = new Profits();
            $ret = $profitTbl->insertRecord(array(
                'type'          => PROFIT_TYPE_TRANSFER,
                'user_id'       => $user_id,
                'currency'      => $params['currency'],
                'profit'        => $fee->__toString(),
            ));

            DB::commit();
            return 0;
        }
        catch (\Exception $ex) {
            Log::error($ex->getMessage());
            DB::rollback();
            return 2;
        }
    }
}
