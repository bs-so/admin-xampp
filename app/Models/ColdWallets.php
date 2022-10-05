<?php

namespace App\Models;

use DB;
use Log;
use Session;
use Litipk\BigNumbers\Decimal;
use App\Modules\CryptoCurrencyAPI;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ColdWallets extends Model
{
    use Notifiable;
	protected $table = 'olc_cold_wallets';

    public static function getDepositWallet($currency) {
        $record = self::where('type', WALLET_TYPE_DEPOSIT)
            ->where('currency', $currency)
            ->where('specified', WALLET_SPECIFIED_DEPOSIT)
            ->first();

        if (!isset($record) || empty($record)) {
            return [];
        }

        return $record->toArray();
    }

    public static function getGasTankWallet() {
        $record = self::where('type', WALLET_TYPE_GASTANK)
            ->where('currency', 'ETH')
            ->where('specified', WALLET_SPECIFIED_GASTANK)
            ->first();

        if (!isset($record) || empty($record)) {
            return [];
        }

        return $record->toArray();
    }

    public static function getWalletById($id) {
        $record = self::where('id', $id)
            ->first();

        if (!isset($record) || empty($record)) {
            return [];
        }

        return $record->toArray();
    }

	public static function getGasValues($currency, &$gas_price, &$gas_limit) {
        $cryptoSettings = Session::get('crypto_settings');

        $gasPriceMode = Master::getValue('GAS_PRICE_MODE');
        $gas_price = Decimal::create($cryptoSettings[$currency]['gas_price']);
        $gas_limit = Decimal::create($cryptoSettings[$currency]['gas_limit']);
        if ($gasPriceMode != GASPRICE_MANUAL) {
            // Get gas price from site
            Log::info(">> Try to get gas price in realtime!!!");
            $realtimeGasPrices = g_getGasPrices();
            if (isset($realtimeGasPrices[$gasPriceMode])) {
                $gwei = Decimal::create('1000000000');
                $gas_price = Decimal::create($realtimeGasPrices[$gasPriceMode] / 10);
                $gas_price = $gas_price->mul($gwei);
                Log::info("     Realtime GasPrice : " . $gas_price->__toString() . "\n");
            }
        }

        $gas_price = $gas_price->__toString();
        $gas_limit = $gas_limit->__toString();

        return true;
    }

    public function getBTCFees() {
        $cryptoSettings = Session::get('crypto_settings');

        $gasPriceMode = Master::getValue('GAS_PRICE_MODE');
        $gas_price = Decimal::create($cryptoSettings[$currency]['gas_price']);
        $gas_limit = Decimal::create($cryptoSettings[$currency]['gas_limit']);
        if ($gasPriceMode != GASPRICE_MANUAL) {
            // Get gas price from site
            Log::info(">> Try to get gas price in realtime!!!");
            $realtimeGasPrices = g_getGasPrices();
            if (isset($realtimeGasPrices[$gasPriceMode])) {
                $gwei = Decimal::create('1000000000');
                $gas_price = Decimal::create($realtimeGasPrices[$gasPriceMode] / 10);
                $gas_price = $gas_price->mul($gwei);
                Log::info("     Realtime GasPrice : " . $gas_price->__toString() . "\n");
            }
        }

        $gas_price = $gas_price->__toString();
        $gas_limit = $gas_limit->__toString();

        return true;
    }

	public function getAll($currency) {
	    $records = DB::table($this->table)
            ->where('status', STATUS_ACTIVE)
            ->where('currency', $currency)
            ->select('*')
            ->get();

	    return $records;
    }

    public function getRecordById($id) {
        $record = DB::table($this->table)
            ->where('id', $id)
            ->select('*')
            ->first();

        return $record;
    }

    public function getNonce($id) {
	    $record = DB::table($this->table)
            ->where('id', $id)
            ->select('nonce')
            ->first();

	    if (!isset($record) || !isset($record->nonce)) {
	        return 0;
        }

	    return $record->nonce;
    }

    public function updateNonce($params) {
	    $ret = DB::table($this->table)
            ->where('id', $params['from_wallet'])
            ->update([
                'nonce'     => $params['nonce'],
            ]);

	    return $ret;
    }

	public function getBalanceSummary($currency) {
	    $records = DB::table($this->table)
            ->where('currency', $currency)
            ->where('status', STATUS_ACTIVE)
            ->groupBy('type')
            ->orderBy('type', 'asc')
            ->select(
                'type',
                'currency',
                DB::raw('count(id) as total_count'),
                DB::raw('sum(balance) as total_balance')
            )
            ->get();

	    if (!isset($records) || count($records) == 0) {
	        return [];
        }

	    return $records;
    }

    public function getTotalBalance($currency) {
        $record = self::where('currency', $currency)
            ->where('status', STATUS_ACTIVE)
            ->select(DB::raw('sum(balance) as total'))
            ->first();

        if (!isset($record) || !isset($record->total)) {
            return 0;
        }

        return $record->total;
    }

    public function refreshBalance($params) {
        $currency = $params['currency'];

        $selector = DB::table($this->table)
            ->where('currency', $currency)
            ->select('*');

        if (isset($params['id'])) {
            $selector->where('id', $params['id']);
        }
        if (isset($params['type'])) {
            $selector->where('type', $params['type']);
        }

        $records = $selector->get();
        $cryptoSettings = Session::get('crypto_settings');
        foreach ($records as $index => $record) {
            $address = $record->wallet_address;

            $balanceInfo = CryptoCurrencyAPI::call_get_balance($currency, $address, COIN_NET);
            if (!isset($balanceInfo['result']) ||  $balanceInfo['result'] != CryptoCurrencyAPI::_HTTP_RESPONSE_CODE_0) {
                return 0;
            }

            $balance = Decimal::create(doubleVal($balanceInfo['detail']));
            $unit = Decimal::create($cryptoSettings[$currency]['unit']);
            $temp = Decimal::create(10)->pow($unit);
            $balance = $balance->div($temp);
            $ret = DB::table($this->table)
                ->where('id', $record->id)
                ->update([
                    'balance'       => $balance->__toString(),
                ]);
        }

        return 1;
    }

    public function getBalance($params)
    {

        $currency = $params['currency'];
        $wallet_address = $params['wallet_address'];
        $balanceInfo = [];

        $balanceInfo = CryptoCurrencyAPI::call_get_balance($currency, $wallet_address);

        $cryptoSettings = Session::get('crypto_settings');
        $balance = Decimal::create($balanceInfo['detail']);
        $unit = $cryptoSettings[$currency]['unit'];
        $div = Decimal::create(10);
        $div = $div->pow(Decimal::create($unit));
        $balance = $balance->div($div);
        $balance = $balance->__toString();

        try {
            DB::beginTransaction();
            $selector = DB::table($this->table)
                ->where('wallet_address', '=', $wallet_address)
                ->update([
                    'balance'    => $balance,
                ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => -1,
            ];
        }

        return [
            'success' => 0,
            'balance' => $balance,
        ];
    }

	public function checkAddress($params) {
	    $currency = $params['currency'];
	    $address = $params['address'];

        $balanceInfo = CryptoCurrencyAPI::call_get_balance($currency, $address, COIN_NET);
        if (!isset($balanceInfo['result']) ||  $balanceInfo['result'] != CryptoCurrencyAPI::_HTTP_RESPONSE_CODE_0) {
            return [
                'success' => 1,			// Get Balance Error
            ];
        }

        if (doubleVal($balanceInfo['detail']) > 0) {     // if ($balanceInfo['detail'] == 0) {
            return [
                'success' => 2,			// Non-Zero Balance
            ];
        }

        return [
            'success' => 0,
            'balance' => doubleVal($balanceInfo['detail']),
        ];
    }

    public function addWallet($params) {
	    $ret = DB::table($this->table)
            ->insert([
                'currency'          => $params['currency'],
                'wallet_address'    => $params['address'],
                'wallet_privkey'    => '',
                'balance'           => 0,
                'type'              => $params['type'],
                'specified'         => WALLET_SPECIFIED_NONE,
                'nonce'             => 0,
                'remark'            => $params['remark'],
                'status'            => STATUS_ACTIVE,
            ]);

	    return $ret;
    }

    public function setPrivateKey($params) {
	    $ret = DB::table($this->table)
            ->where('id', $params['id'])
            ->update([
                'wallet_privkey'        => $params['private_key'],
            ]);

	    return $ret;
    }

    public function deleteRecord($id) {
	    $ret = DB::table($this->table)
            ->where('id', $id)
            ->delete();

	    return $ret;
    }

    public function getWalletList($params) {
	    $records = DB::table($this->table)
            ->where('currency', $params['currency'])
            ->where('type', $params['type'])
            ->where('status', STATUS_ACTIVE)
            ->select('*')
            ->get();

	    return $records;
    }

    public function getWithdrawBalance($currency) {
        $records = DB::table($this->table)
            ->where('currency', $currency)
            ->where('type', WALLET_TYPE_WITHDRAW)
            ->select('balance')
            ->get();

        if (!isset($records) || count($records) == 0) {
            return 0;
        }

        return $records[0]->balance;
    }

    public static function getWithdrawWallet($currency) {
        $ret = ColdWallets::where([
            'type' => WALLET_TYPE_WITHDRAW,
            'currency' => $currency,
            'specified' => WALLET_SPECIFIED_WITHDRAW
        ])->first();
        if (!empty($ret)) {
            return $ret->toArray();
        }
        return [];
    }

    public function specifyWallet($params) {
	    $ret = DB::table($this->table)
            ->where('currency', $params['currency'])
            ->where('specified', $params['specified'])
            ->update([
                'specified' => WALLET_SPECIFIED_NONE,
            ]);

	    $ret = DB::table($this->table)
            ->where('id', $params['id'])
            ->update([
                'specified' => $params['specified'],
            ]);

	    return $ret;
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->where('currency', $params['currency'])
            ->select(
                '*'
            );

        // filtering
        if (isset($params['type'])) {
            $selector->where('type', $params['type']);
        }
        $recordsTotal = $selector->count();

        if (isset($params['columns'][2]['search']['value'])
            && $params['columns'][2]['search']['value'] !== ''
        ) {
            $selector->where('type', $params['columns'][2]['search']['value']);
        }
        if (isset($params['columns'][4]['search']['value'])
            && $params['columns'][4]['search']['value'] !== ''
        ) {
            $selector->where('wallet_address', 'like', '%' . $params['columns'][4]['search']['value'] . '%');
        }
        if (isset($params['columns'][7]['search']['value'])
            && $params['columns'][7]['search']['value'] !== ''
        ) {
            $selector->where('status', $params['columns'][7]['search']['value']);
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
