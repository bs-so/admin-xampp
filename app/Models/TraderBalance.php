<?php

namespace App\Models;

use DB;
use Log;
use Litipk\BigNumbers\Decimal;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TraderBalance extends Model
{
    use Notifiable;
    protected $table = 'olc_users_balance';
    protected $table_user_withdraw = 'olc_users_withdraw';

    public static function addDefaultBalance($user_id) {
        $ret = true;

        foreach (g_enum('DefaultBalances') as $currency => $amount) {
            $record = self::where('user_id', $user_id)
                ->where('currency', $currency)
                ->select('id', 'balance')
                ->first();

            if (!isset($record) || !isset($record->balance)) {
                // Insert new record
                $ret = self::insert([
                    'user_id'   => $user_id,
                    'currency'  => $currency,
                    'balance'   => $amount,
                ]);
            }
        }

        return $ret;
    }

    public static function updateSettleBalance($user_balances) {
        try {
            DB::beginTransaction();

            foreach ($user_balances as $user_id => $records) {
                foreach ($records as $currency => $data) {
                    $record = self::where('user_id', $user_id)
                        ->where('currency', $currency)
                        ->select('id', 'balance')
                        ->lockForUpdate()
                        ->first();

                    if (!isset($record) || !isset($record->id)) {
                        // Insert
                        $ret = self::insert([
                            'user_id'   => $user_id,
                            'currency'  => $currency,
                            'balance'   => $data['next_balance'],
                        ]);
                    }
                    else {
                        // Update
                        $ret = self::where('id', $record->id)
                            ->update([
                                'balance'       => $data['next_balance'],
                            ]);
                    }
                }
            }

            DB::commit();

            return true;
        }
        catch (\Exception $ex) {
            DB::rollback();
            Log::channel('settle')->error($ex->getMessage());

            return false;
        }
    }

    public function rollbackBalances($params) {
        $records = DB::table($this->table_user_withdraw)
            ->whereIn('id', $params['selected'])
            ->groupBy('user_id')
            ->select('user_id', 'currency', DB::raw('sum(amount) as total'))
            ->get();

        if (!isset($records) || count($records) == 0) {
            return false;
        }

        $ret = true;
        foreach ($records as $index => $record) {
            $user_id = $record->user_id;
            $currency = $record->currency;
            $total = $record->total;

            $ret = self::increaseBalance($user_id, $currency, $total);
        }

        return $ret;
    }

    public function getUserBalance($user_id, $currency) {
        $record = self::where('user_id', $user_id)
            ->where('currency', $currency)
            ->select('balance')
            ->first();

        if (!isset($record) || !isset($record->balance)) {
            return 0;
        }

        return $record->balance;
    }

    public static function increaseBalance($user_id, $currency, $amount) {
        try {
            DB::beginTransaction();
            $temp = self::where('user_id', $user_id)
                ->where('currency', $currency)
                ->select('id', 'balance')
                ->lockForUpdate()
                ->first();

            if (isset($temp) && isset($temp->balance)) {
                $balance = $temp->balance;
                $balance = Decimal::create($balance)->add(Decimal::create($amount));

                $ret = self::where('id', $temp->id)
                    ->update([
                        'balance' => $balance->__toString(),
                    ]);
            }
            else {
                $ret = self::insert([
                    'user_id'   => $user_id,
                    'currency'  => $currency,
                    'balance'   => $amount,
                ]);
            }

            DB::commit();
            return $ret;
        }
        catch (\Exception $ex) {
            DB::rollback();
            Log::error($ex->getMessage());
            return aflse;
        }
    }

    public static function updateBalanceByWhere($where, $data) {
        $record = self::where($where)->first();

        if (!isset($record) || empty($record)) {
            $record = self::insert([
                'user_id'   => isset($where['user_id']) ? $where['user_id'] : 0,
                'currency'  => $where['currency'],
                'balance'   => $data['balance'],
            ]);

			return true;
        }

        try {
            $balance = Decimal::create($record->balance);
            $balance = $balance->add(Decimal::create($data['balance']));
            $record->balance = $balance->__toString();

            $record->save();
        } catch(\Exception $e) {
            Log::debug($e->getMessage());
            return false;
        }

        return true;
    }

    public function getDataById($user_id) {
        $records = self::where('user_id', $user_id)
            ->pluck('balance', 'currency');

        if (!isset($records) || count($records) == 0) {
            return [];
        }

        return $records;
    }
}
