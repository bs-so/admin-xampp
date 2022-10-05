<?php

namespace App\Console\Commands;

use DB;
use Log;
use Session;
use App\Models\CryptoSettings;
use App\Models\GasUsage;
use App\Models\Profits;
use App\Models\SystemProfit;
use App\Models\Master;
use App\Models\SystemBalance;
use App\Models\TraderBalance;
use App\Models\TraderWithdraw;
use App\Models\ColdWallets;
use App\Models\RealtimeLogs;
use App\Models\TraderWithdrawQueue;
use App\Models\StaffWithdrawQueue;
use App\Modules\CryptoCurrencyAPI;
use Litipk\BigNumbers\Decimal;
use Illuminate\Console\Command;

class CheckUserWithdraw extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check-user-withdraw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check user withdraw';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $is_linux = true;
        if (strcasecmp(substr(PHP_OS, 0, 3), 'WIN') == 0){
            $is_linux = false;
        }

        $semaphore = 0;
        if ($is_linux) {
            $semaphore = sem_get(2021021803, 1);
            if (empty($semaphore)) {
                echo 'Failed to get semaphore';
                return 0;
            }
            if (!sem_acquire($semaphore, true)) {
                echo 'Another command is running already';
                return 0;
            }
        }

        Log::channel('crypto')->info("-------------------------- Check user withdraw --------------------------");

        $autoProcess = Master::getValue(AUTO_USER_WITHDRAW);
        if ($autoProcess != STATUS_ACTIVE) {
            print_r("Auto process has disabled\n");
            Log::channel('crypto')->info("Auto process has disabled");
            return 0;
        }

        // Check staff withdraw
        $withdrawList = StaffWithdrawQueue::whereIn('status', [WITHDRAW_QUEUE_STATUS_REQUESTED])->get()->toArray();
        if (isset($withdrawList) && count($withdrawList) > 0) {
            // Staff withdraw exists!
            print_r("!!! Staff withdraw is processing!" . "\n");
            Log::channel('crypto')->info("!!! Staff withdraw is processing!");
            return 0;
        }

        $cryptoSettings = Session::get('crypto_settings');
        if (!isset($cryptoSettings) || count($cryptoSettings) == 0) {
            $cryptoSettings = CryptoSettings::getAll();
            Session::put('crypto_settings');
        }
        $withdrawFee = Decimal::create(Master::getValue(WITHDRAW_FEE));
        $gasPriceMode = Master::getValue(GAS_PRICE_MODE);

        $profitTbl = new Profits();
        $systemProfitTbl = new SystemProfit();
        $systemBalanceTbl = new SystemBalance();
        $realtimeLogsTbl = new RealtimeLogs();
        foreach ($cryptoSettings as $currency => $info) {
            if ($info['status'] != STATUS_ACTIVE) continue;
            $ret = $realtimeLogsTbl->insertRecord(LOGS_TYPE_USER_WITHDRAW, $currency);
        }

        $withdrawList = TraderWithdrawQueue::whereIn('status', [WITHDRAW_QUEUE_STATUS_REQUESTED])->get()->toArray();
        foreach ($withdrawList as $record) {
            if (isset($record['tx_id']) && $record['tx_id'] != '') {
                print_r("    " . $record['id'] . " : Tx.ID already exist!!! Tx.ID = " . $record['tx_id'] . "\n");
                Log::channel('crypto')->info("    " . $record['id'] . " : Tx.ID already exist!!! Tx.ID = " . $record['tx_id']);
                continue;
            }
            $withdrawId = $record['withdraw_id'];
            $currency = $record['currency'];
            $to_address = $record['to_address'];
            $amount = $record['amount'];
            $balance = Decimal::create($amount);

            print_r("*** <<" . "Withdraw currency:" . $currency . ", to addr: " . $to_address . ", amount: " . $amount . "\n");
            Log::channel('crypto')->info("*** <<" . "Withdraw currency:" . $currency . ", to addr: " . $to_address . ", amount: " . $amount);

            if ($currency == 'BTC') {
                $proc_list = TraderWithdraw::where('id', '!=', $withdrawId)
                    ->where('currency', $currency)
                    ->whereIn('status', [STATUS_PENDING])
                    ->get();
                $checking = false;
                foreach ($proc_list as $proc_index => $proc_record) {
                    $proc_ret = TraderWithdrawQueue::where('withdraw_id', $proc_record->id)
                        ->select('status')
                        ->get();
                    if (isset($proc_ret) && isset($proc_ret[0]->status) && $proc_ret[0]->status == WITHDRAW_QUEUE_STATUS_FINISHED) {
                        $checking = true;
                        break;
                    }
                }
                if ($checking) {
                    print_r("!!!!! BTC withdraw is already processing!!!" . "\n");
                    Log::channel('crypto')->info("!!!!! BTC withdraw is already processing!!!");
                    continue;
                }
            }

            // 1. Get Cold Wallet Address
            $withdraw_wallet = ColdWallets::getWalletById($record['cold_wallet_id']);
            if (empty($withdraw_wallet)) {
                Log::channel('crypto')->info("    No withdraw wallet! currency = " . $currency);
                continue;
            }
            print_r("*** Get Cold Wallet Address, id: " . $withdraw_wallet['id'] . "\n");
            Log::channel('crypto')->info("*** Get Cold Wallet Address,id:".$withdraw_wallet['id']);

            $from_address = $withdraw_wallet['wallet_address'];
            $privkey = $withdraw_wallet['wallet_privkey'];

            // 2. Check withdraw fee & minium amount
            $withdrawMinAmount = Decimal::create($cryptoSettings[$currency]['min_withdraw']);
            if ($balance->isLessThan($withdrawMinAmount)) {
                print_r("*** Less than withdraw min amount. MinAmount = " . $withdrawMinAmount->__toString() . "\n");
                Log::channel('crypto')->info('*** Less than withdraw min amount. MinAmount = ' . $withdrawMinAmount->__toString());
                continue;
            }

            $trans_fee = Decimal::create('0');
            $pow = Decimal::create(10);
            $pow = $pow->pow(Decimal::create($cryptoSettings[$currency]['unit']));
            if ($currency != 'USDT' && $currency != 'ETH') {
                $trans_fee = Decimal::create($cryptoSettings[$currency]['transfer_fee']);
                $temp_trans_fee = $trans_fee->mul($pow);
            }

            // Apply withdraw fee
            $profit = Decimal::create($balance);
            $profit = $profit->mul($withdrawFee);
            $profit = $profit->div(Decimal::create('100'));

            $realProfit = Decimal::create($profit);
            $realProfit = $realProfit->sub($trans_fee);
            if ($realProfit->isNegative()) {
                // No system profit
                print_r("    No system profit!!! SystemProfit: " . $realProfit->__toString() . "\n");
                Log::channel('crypto')->info("    No system profit!!! Currency: " . $currency . "SystemProfit: " . $realProfit->__toString());
                continue;
            }

            // !!!!!!!!!!! Start Withdraw !!!!!!!!

            // Send action starts!!!
            $realtimeLogsTbl = new RealtimeLogs();
            $ret = $realtimeLogsTbl->insertRecord(LOGS_TYPE_USER_WITHDRAW, $currency);

            $withdrawAmount = Decimal::create($balance);
            $withdrawAmount = $withdrawAmount->sub($profit);

            $temp_withdrawAmount = Decimal::create($withdrawAmount);
            $temp_withdrawAmount = $temp_withdrawAmount->mul($pow);

            $tx_id = '';
            // 3. Check for need gas
            if ($currency == 'ETH' || $currency == 'USDT') {
                // 3.0 Check Currency Balance
                $balInfo = CryptoCurrencyAPI::call_get_balance($currency, $from_address, COIN_NET);
                $main_balance = Decimal::create((isset($balInfo['detail'])) ? $balInfo['detail'] : '0');
                if ($main_balance->isLessThan($withdrawAmount)) {
                    // Not enough balance
                    print_r("     Not enough balance(" . $currency . "), " . $main_balance->__toString() . ', ' . $withdrawAmount->__toString() . "\n");
                    Log::channel('crypto')->info("     Not enough balance(" . $currency . "), " . $main_balance->__toString() . ', ' . $withdrawAmount->__toString());
                    $ret = TraderWithdraw::updateRecords(['id' => $withdrawId], ['status' => STATUS_NO_BALANCE]);
                    continue;
                }

                $ret = TraderWithdrawQueue::updateStateByWhere(
                    ['id' => $record['id'], 'currency' => $record['currency'], 'status' => $record['status']],
                    ['status' => WITHDRAW_QUEUE_STATUS_PROCESSING]
                );
                if ($ret === false) {
                    print_r("    Update status has failed!\n");
                    Log::channel('crypto')->info("    Update status has failed!");
                    continue;
                }

                // 3.1 Check ETH balance
                $gasInfo = CryptoCurrencyAPI::call_get_balance('ETH', $from_address, COIN_NET);
                $gas_balance = Decimal::create((isset($gasInfo['detail'])) ? $gasInfo['detail'] : '0');

                if ($currency == 'ETH') {
                    $gas_balance = $gas_balance->sub($withdrawAmount);
                }

                print_r("    Now exist balance: " . $gas_balance->__toString() . "\n");
                Log::channel('crypto')->info("    Now exist balance: " . $gas_balance->__toString());

                $gas_price = Decimal::create($cryptoSettings[$currency]['gas_price']);
                $gas_limit = Decimal::create($cryptoSettings[$currency]['gas_limit']);
                $gas_need = Decimal::create($cryptoSettings[$currency]['gas']);
                $eth_gas_price = $cryptoSettings['ETH']['gas_price'];
                if ($gasPriceMode != GASPRICE_MANUAL) {
                    // Get gas price from site
                    print_r("    Try to get gas price in realtime!!!\n");
                    Log::channel('crypto')->info("    Try to get gas price in realtime!!!");
                    $realtimeGasPrices = g_getGasPrices();
                    if (isset($realtimeGasPrices[$gasPriceMode])) {
                        $gwei = Decimal::create('1000000000');
                        $gas_price = Decimal::create($realtimeGasPrices[$gasPriceMode] / 10);
                        $gas_price = $gas_price->mul($gwei);
                        $gas_need = $gas_price->mul($gas_limit);
                        $gas_need = $gas_need->div($gwei)->div($gwei);
                        $eth_gas_price = $gas_price->__toString();
                        print_r("     Realtime GasPrice : " . $gas_price->__toString() . "\n");
                        Log::channel('crypto')->info("     Realtime GasPrice : " . $gas_price->__toString() . "\n");
                    }
                }
                print_r("     Applying GasPrice : " . $gas_price->__toString() . ", Gas : " . $gas_need->__toString() . "\n");
                Log::channel('crypto')->info("     Applying GasPrice : " . $gas_price->__toString() . ", Gas : " . $gas_need->__toString());

                if ($gas_balance->isLessThan($gas_need)) {
                    // 3.2 Require gas from Gastank
                    print_r("    Require gas from Gastank!!!". "\n");
                    Log::channel('crypto')->info("    Require gas from Gastank!!!");

                    $gastank_wallet = ColdWalletS::GetGasTankWallet();
                    $ret = CryptoCurrencyAPI::call_send(
                        'ETH',
                        $gastank_wallet['wallet_privkey'],
                        $from_address,
                        $gas_need->__toString(),
                        '',
                        COIN_NET,
                        $eth_gas_price,
                        $cryptoSettings['ETH']['gas_limit']
                    );

                    if (!isset($ret['result'])) {
                        Log::channel('crypto')->info("*** Call Send API failed. >>");
                        continue;
                    }
                    if ($ret['result'] != 0) {
                        Log::channel('crypto')->info("*** Call Send API : code - " . $ret['result'] . " >>");
                        continue;
                    }

                    $tx_id = $ret['detail']['hash'];
                    print_r("    Sent gas request to GasTank. TX_ID = " . $tx_id . "\n");
                    Log::channel('crypto')->info("    Sent gas request to GasTank. TX_ID = " . $tx_id);

                    // 3.3 Waiting for transaction complete
                    Log::channel('crypto')->info('    Waiting for gas receive..');
                    while (true) {
                        $ret = CryptoCurrencyAPI::call_checktransaction('ETH', $tx_id);

                        if (!isset($ret['result'])) {
                            Log::channel('crypto')->info("*** Call CheckTransaction API failed. >>");
                            sleep(1);
                            continue;
                        }
                        if ($ret['result'] != 0) {
                            Log::channel('crypto')->info("*** Call CheckTransaction API : code - " . $ret['result'] . " >>");
                            sleep(1);
                            continue;
                        }

                        break;
                    }

                    $gas_eth_used = Decimal::create($ret['detail']['gasUsed']);
                    $gas_eth_used = $gas_eth_used->mul(Decimal::create($eth_gas_price));
                    $gas_eth_used = $gas_eth_used->div(Decimal::create(GAS_UNIT));
                    $gas_eth_used = $gas_eth_used->div(Decimal::create(GAS_UNIT));
                    Log::channel('crypto')->info("Received gas. Used gas for ETH: " . $gas_eth_used->__toString());
                    $gasUsageTbl = new GasUsage();
                    $ret = $gasUsageTbl->insertRecord([
                        'currency'      => $currency,
                        'tx_id'         => $tx_id,
                        'to_address'    => $from_address,
                        'gas_sent'      => $gas_need->__toString(),
                        'gas_used'      => $gas_eth_used->__toString(),
                        'remark'        => 'User-Withdraw',
                    ]);
                }

                print_r("*** Try to Call Send API..." . "\n");
                Log::channel('crypto')->info("*** Try to Call Send API...");

                // 3.5 Send token
                $ret = CryptoCurrencyAPI::call_send(
                    $currency,
                    $privkey,
                    $to_address,
                    $withdrawAmount->__toString(),
                    '',
                    COIN_NET,
                    $gas_price->__toString(),
                    $gas_limit->__toString()
                );

                if (!isset($ret['result'])) {
                    Log::channel('crypto')->info("*** Call Send API failed. >>");
                    continue;
                }
                if ($ret['result'] != 0) {
                    Log::channel('crypto')->info("*** Call Send API : code - " . $ret['result'] . ", " . $ret['detail'] . " >>");
                    continue;
                }

                // 3. Waiting for transaction complete
                $tx_id = $ret['detail']['hash'];
                $success = true;
                print_r("#### Waiting for transaction complete.. TX_ID = " . $tx_id . "\n");
                Log::channel('crypto')->info('#### Waiting for transaction complete.. TX_ID = ' . $tx_id);
                while (true) {
                    $ret = CryptoCurrencyAPI::call_checktransaction('ETH', $tx_id);

                    if (!isset($ret['result'])) {
                        Log::channel('crypto')->info("*** Call CheckTransaction API failed. >>");
                        sleep(1);
                        continue;
                    }
                    if ($ret['result'] == 32) {
                        // Failed
                        $success = false;
                        break;
                    }
                    if ($ret['result'] != 0) {
                        Log::channel('crypto')->info("*** Call CheckTransaction API : code - " . $ret['result'] . " >>");
                        sleep(1);
                        continue;
                    }

                    break;
                }
                if (!$success) {
                    // Transaction failed
                    print_r("*** Transaction has failed!!! TX : " . $tx_id . "\n");
                    Log::channel('crypto')->info("*** Transaction has failed!!! TX : " . $tx_id);
                    $updateResult = TraderWithdraw::updateRecords([
                        'id'    => $withdrawId,
                    ], [
                        'tx_id' => $tx_id,
                        'status' => STATUS_FAILED
                    ]);
                    continue;
                }

                $gasUsed = $ret['detail']['gasUsed'];
                print_r("*** Transaction Complete!!! gasUsed = " . $gasUsed . "\n");
                Log::channel('crypto')->info("*** Transaction Complete!!! gasUsed = " . $gasUsed);

                $trans_fee = Decimal::create($gasUsed);
                $trans_fee = $trans_fee->mul($gas_price);
                $trans_fee = $trans_fee->div(Decimal::create(GAS_UNIT));
                $trans_fee = $trans_fee->div(Decimal::create(GAS_UNIT));

                // 4. Update Withdraw Record
                $journal_record = [];
                $updateResult = TraderWithdraw::updateRecords([
                    'id'    => $withdrawId,
                ], [
                    'tx_id' => $tx_id,
                    'withdraw_fee'  => $profit->__toString(),
                    'transfer_fee'  => $trans_fee->__toString(),
                    'gas_price'     => $gas_price->__toString(),
                    'gas_used'      => $gas_price->__toString(),
                    'status'        => STATUS_ACCEPTED,
                ]);
                echo("*** Update Withdraw Record" . "\n");
                Log::channel('crypto')->info("*** Create Withdraw Record");

                /*$notify_ret = UserNotify::insertRecord([
                    'user_id'   => $record['user_id'],
                    'type'      => USER_NOTIFY_TYPE_WITHDRAW,
                    'currency'  => $currency,
                    'amount'    => $withdrawAmount->__toString(),
                ]);*/

                // 6. Add Profit
                $ret = $profitTbl->insertRecord([
                    'user_id'   => $record['user_id'],
                    'currency'  => $currency,
                    'profit'    => $profit->__toString(),
                    'type'      => PROFIT_TYPE_WITHDRAW,
                ]);
                $ret = $systemProfitTbl->insertRecord([
                    'user_id'   => $record['user_id'],
                    'currency'  => $currency,
                    'profit'    => $profit->__toString(),
                ], SYSTEM_PROFIT_TYPE_WALLET);
                $ret = $systemBalanceTbl->addProfit($record['user_id'], SYSTEM_BALANCE_TYPE_WALLET, $currency, $profit->__toString());
                echo("*** Add profit has finished!" . "\n");
                Log::channel('crypto')->info("*** Add profit has finished!");
            }
            else {
                // Check currency balance
                $balInfo = CryptoCurrencyAPI::call_get_balance($currency, $from_address, COIN_NET);
                $main_balance = Decimal::create((isset($balInfo['detail'])) ? $balInfo['detail'] : '0');
                $main_balance = $main_balance->div($pow);
                if ($main_balance->isLessThan($withdrawAmount)) {
                    // Not enough balance
                    print_r("     Not enough balance(" . $currency . "), " . $main_balance->__toString() . ', ' . $withdrawAmount->__toString() . "\n");
                    Log::channel('crypto')->info("     Not enough balance(" . $currency . "), " . $main_balance->__toString() . ', ' . $withdrawAmount->__toString());
                    $ret = TraderWithdraw::updateRecords(['id' => $withdrawId], ['status' => STATUS_NO_BALANCE]);
                    continue;
                }

                $ret = TraderWithdrawQueue::updateStateByWhere(
                    ['id' => $record['id'], 'currency' => $record['currency'], 'status' => $record['status']],
                    ['status' => WITHDRAW_QUEUE_STATUS_PROCESSING]
                );
                if ($ret === false) {
                    print_r("    Update status has failed!\n");
                    Log::channel('crypto')->info("    Update status has failed!");
                    continue;
                }

                // 3. Withdraw to user wallet.
                $ret = CryptoCurrencyAPI::call_send(
                    $currency,
                    $privkey,
                    $to_address,
                    $temp_withdrawAmount->__toString(),
                    $temp_trans_fee->__toString(),
                    COIN_NET
                );

                if (!isset($ret['result'])) {
                    Log::channel('crypto')->info("*** Call Send API failed. >>");
                    continue;
                }
                if ($ret['result'] != 0) {
                    Log::channel('crypto')->info("*** Call Send API : code - " . $ret['result'] . " >>");
                    continue;
                }

                echo("*** Call Send API success!,trans_fee: " . $trans_fee->__toString() . "\n");
                Log::channel('crypto')->info("*** Call Send API success!,trans_fee: " . $trans_fee->__toString() . "detail:" . json_encode($ret['detail']));
                $tx_id = $ret['detail']['hash'];

                $updateResult = TraderWithdraw::updateRecords([
                    'id'    => $withdrawId,
                ], [
                    'tx_id' => $tx_id,
                    'withdraw_fee'  => $profit->__toString(),
                    'transfer_fee'  => $trans_fee->__toString(),
                    'gas_price'     => 0,
                    'gas_used'      => 0,
                    //'status'        => STATUS_ACCEPTED,
                ]);

                /*$notify_ret = UserNotify::insertRecord([
                    'user_id'   => $record['user_id'],
                    'type'      => USER_NOTIFY_TYPE_WITHDRAW,
                    'currency'  => $currency,
                    'amount'    => $withdrawAmount->__toString(),
                ]);*/

                // 6. Add Profit
                $ret = $profitTbl->insertRecord([
                    'user_id'   => $record['user_id'],
                    'currency'  => $currency,
                    'profit'    => $profit->__toString(),
                    'type'      => PROFIT_TYPE_WITHDRAW,
                ]);
                $ret = $systemProfitTbl->insertRecord([
                    'user_id'   => $record['user_id'],
                    'currency'  => $currency,
                    'profit'    => $profit->__toString(),
                ], SYSTEM_PROFIT_TYPE_WALLET);
				$ret = $systemBalanceTbl->addProfit($record['user_id'], SYSTEM_BALANCE_TYPE_WALLET, $currency, $profit->__toString());
                echo("*** Add profit has finished!" . "\n");
                Log::channel('crypto')->info("*** Add profit has finished!");
            }

            // 5. Update status
            $ret = TraderWithdrawQueue::updateState($record['id'], $tx_id, WITHDRAW_QUEUE_STATUS_FINISHED);
            Log::channel('crypto')->info("*** Update withdraw queue status");

            print_r("*** Finished! >> ***" . "\n");
            Log::channel('crypto')->info("*** Finished! >> ***");
        }

        if ($is_linux) {
            sem_release($semaphore);
        }

        return 0;
    }
}
