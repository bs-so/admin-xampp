<?php

namespace App\Console\Commands;

use DB;
use Log;
use Session;
use App\Models\Master;
use App\Models\Trader;
use App\Models\Profits;
use App\Models\TraderWithdraw;
use App\Models\SystemProfit;
use App\Models\TraderBalance;
use App\Models\GasUsage;
use App\Models\ColdWallets;
use App\Models\TraderDeposit;
use App\Models\DepositQueue;
use App\Models\TraderDisposable;
use App\Models\RealtimeLogs;
use App\Models\CryptoSettings;
use App\Modules\CryptoCurrencyAPI;
use Litipk\BigNumbers\Decimal;
use Illuminate\Console\Command;

class CheckUserDeposit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check-user-deposit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks user deposit';

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
            $semaphore = sem_get(2021021601, 1);
            if (empty($semaphore)) {
                echo 'Failed to get semaphore';
                return;
            }
            if (!sem_acquire($semaphore, true)) {
                echo 'Another command is running already';
                return;
            }
        }

        $cryptoSettings = Session::get('crypto_settings');
        if (!isset($cryptoSettings) || count($cryptoSettings) == 0) {
            $cryptoSettings = CryptoSettings::getAll();
            Session::put('crypto_settings');
        }
        $gasPriceMode = Master::getValue('GAS_PRICE_MODE');
        $depositFee = Decimal::create(Master::getValue('DEPOSIT_FEE'));

        print_r("!!!!!!!!!!!!!!!!!! --- Check User Deposit --- !!!!!!!!!!!!!!!!!!" . "\n");
        Log::channel('crypto')->info("!!!!!!!!!!!!!!!!!! --- Check User Deposit --- !!!!!!!!!!!!!!!!!!");

        $profitTbl = new Profits();
        $systemProfitTbl = new SystemProfit();
        $tbl = new DepositQueue();
        $queueLists = $tbl->pickTop();
        print_r(">> Queue List Count : " . count($queueLists) . "\n");
        Log::channel('crypto')->info(">> Queue List Count : " . count($queueLists));
        $depositTbl = new TraderDeposit();

        $realtimeLogsTbl = new RealtimeLogs();
        foreach ($cryptoSettings as $currency => $info) {
            if ($info['status'] != STATUS_ACTIVE) continue;
            $ret = $realtimeLogsTbl->insertRecord(LOGS_TYPE_USER_DEPOSIT, $currency);
        }

        foreach ($queueLists as $index => $queue) {
            $req_id = $queue->user_id;
			$count = TraderWithdraw::getProcessingCount($req_id);
			if ($count > 0) {
				print_r("    There's processing entry yet.\n");
				Log::channel('crypto')->info("    There's processing entry yet.");
				continue;
			}
            $walletLists = TraderDisposable::where([
                'confirmed' => DISPOSABLE_STATUS_NEED,
                'status'    => STATUS_ACTIVE,
                'user_id'   => $req_id,
            ])->get()->toArray();
            $ret = $tbl->deleteRecords($req_id);

            foreach ($walletLists as $record) {
                $currency = $record['currency'];
                $wallet_address = $record['wallet_address'];

                print_r('>> Checking wallets : ' . $currency . ', ' . $wallet_address . "\n");
                Log::channel('crypto')->info("");
                Log::channel('crypto')->info('>> Checking wallets : ' . $currency . ', ' . $wallet_address);

                // 1. Check Wallet Balance
				sleep(4);
                $balanceInfo = $this->checkWalletDeposit($currency, $wallet_address);
                if ($balanceInfo == false) {
                    echo('>> Currency:' . $record['currency'] . ',from addr:' . $record['wallet_address'] . ' not yet.' . "\n");
                    continue;
                }

                $balance = Decimal::create($balanceInfo);
                $pow = Decimal::create(10);
                $pow = $pow->pow(Decimal::create($cryptoSettings[$currency]['unit']));
                $balance = $balance->div($pow);

                echo('    Deposit currency:' . $currency . ',addr:' . $record['wallet_address'] . ' deposit success!.",amount:' . $balance->__toString() . "\n");
                Log::channel('crypto')->info("    " . ' Deposit currency:' . $currency . ',addr:' . $record['wallet_address'] . ',amount:' . $balance->__toString());

                if ($currency == 'XRP' && doubleVal($balanceInfo) <= 20) {
                    Log::channel('crypto')->info("    XRP balance not enough, less than 20. >>>");
                    continue;
                }

                // 2. Check deposit fee & minium amount
                $depositMinAmount = Decimal::create($cryptoSettings[$currency]['min_deposit']);
                if ($balance->isLessThan($depositMinAmount)) {
                    print_r('    Less than deposit min amount. MinAmount = ' . $depositMinAmount->__toString() . "\n");
                    Log::channel('crypto')->info('    Less than deposit min amount. MinAmount = ' . $depositMinAmount->__toString());
                    continue;
                }

                $trans_fee = Decimal::create('0');
                if ($currency != 'USDT' && $currency != 'ETH') {
                    $trans_fee = Decimal::create($cryptoSettings[$currency]['transfer_fee']);
                    $pow = Decimal::create(10);
                    $pow = $pow->pow(Decimal::create($cryptoSettings[$currency]['unit']));
                    $temp_trans_fee = $trans_fee->mul($pow);
                }

                // Apply deposit fee
                $profit = Decimal::create($balance);
                $profit = $profit->mul($depositFee);
                $profit = $profit->div(Decimal::create('10000'));

                $realProfit = Decimal::create($profit);
                $realProfit = $realProfit->sub($trans_fee);

                $depositAmount = $balance->sub($trans_fee);
                $userBalance = $balance->sub($profit);

                /*if ($realProfit->isNegative()) {
                    // No admin profit
                    Log::channel('crypto')->info('    No real profit!!! Profit = ' . $profit->__toString() . ', TransFee = ' . $trans_fee->__toString() . ', RealProfit = ' . $realProfit->__toString());
                    continue;
                }

                echo('*** RealProfit = ' . $realProfit->__toString() . "\n");
                Log::channel('crypto')->info('    RealProfit = ' . $realProfit->__toString());*/

                $deposit_wallet = ColdWallets::getDepositWallet($currency);
                if (empty($deposit_wallet)) {
                    print_r("### Can't find cold wallet! " . $currency . "\n");
                    Log::channel('crypto')->info("### Can't find cold wallet! " . $currency);
                    continue;
                }

                // Send action starts!!!
                $realtimeLogsTbl = new RealtimeLogs();
                $ret = $realtimeLogsTbl->insertRecord(LOGS_TYPE_USER_DEPOSIT, $currency);

                if ($currency == 'USDT' || $currency == 'ETH') {
                    // For token
                    //1. Check GAS
                    $gas_balance = Decimal::create($this->checkWalletDeposit('ETH', $record['wallet_address']));
                    Log::channel('crypto')->info('Now gas : ' . $gas_balance->__toString());

                    $gas_price = Decimal::create($cryptoSettings[$currency]['gas_price']);
                    $gas_limit = Decimal::create($cryptoSettings[$currency]['gas_limit']);
                    $gas_need = Decimal::create($cryptoSettings[$currency]['gas']);
                    $eth_gas_price = $cryptoSettings['ETH']['gas_price'];
                    if ($gasPriceMode != GASPRICE_MANUAL) {
                        // Get gas price from site
                        print_r("### Try to get gas price in realtime!!!\n");
                        Log::channel('crypto')->info("");
                        Log::channel('crypto')->info("### Try to get gas price in realtime!!!");
                        $realtimeGasPrices = g_getGasPrices();
                        if (isset($realtimeGasPrices[$gasPriceMode])) {
                            $gwei = Decimal::create('1000000000');
                            $gas_price = Decimal::create($realtimeGasPrices[$gasPriceMode] / 10);
                            $gas_price = $gas_price->mul($gwei);
                            $gas_need = $gas_price->mul($gas_limit);
                            $gas_need = $gas_need->div($gwei)->div($gwei);
                            $eth_gas_price = $gas_price->__toString();
                            print_r("### Realtime GasPrice : " . $gas_price->__toString() . "\n");
                            Log::channel('crypto')->info("### Realtime GasPrice : " . $gas_price->__toString() . "\n");
                        }
                    }
                    print_r("### Applying GasPrice : " . $gas_price->__toString() . ", Gas : " . $gas_need->__toString() . "\n");
                    Log::channel('crypto')->info("### Applying GasPrice : " . $gas_price->__toString() . ", Gas : " . $gas_need->__toString());

                    if ($gas_balance->sub($gas_need)->isNegative()) {
                        // 1.1 Request gas to gastank(if ETH --> always)
                        $gastank_wallet = ColdWallets::getGasTankWallet();

                        $ret = CryptoCurrencyAPI::call_send(
                            'ETH',
                            $gastank_wallet['wallet_privkey'],
                            $record['wallet_address'],
                            $gas_need->__toString(),
                            '',
                            COIN_NET,
                            $eth_gas_price,
                            $cryptoSettings['ETH']['gas_limit']
                        );

                        Log::channel('crypto')->info($ret);

                        if (!isset($ret['result'])) {
                            Log::channel('crypto')->info("*** Call Send API failed. >>");
                            continue;
                        }
                        if ($ret['result'] != 0) {
                            Log::channel('crypto')->info("*** Call Send API : code - " . $ret['result'] . " >>");
                            continue;
                        }

                        $tx_id = $ret['detail']['hash'];
                        Log::channel('crypto')->info("");
                        Log::channel('crypto')->info("### Sent gas request to GasTank. TX_ID = " . $tx_id);

                        // 1.2 Waiting for transaction complete
                        print_r('    Waiting for gas receive..' . "\n");
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

                        // The gas for ETH doesn't change!!!
                        $gas_eth_used = Decimal::create($ret['detail']['gasUsed']);
                        $gas_eth_used = $gas_eth_used->mul(Decimal::create($eth_gas_price));
                        $gas_eth_used = $gas_eth_used->div(Decimal::create(GAS_UNIT));
                        $gas_eth_used = $gas_eth_used->div(Decimal::create(GAS_UNIT));
                        print_r("    Received gas. Used gas for ETH: " . $gas_eth_used->__toString() . "\n");
                        Log::channel('crypto')->info("    Received gas. Used gas for ETH: " . $gas_eth_used->__toString());
                        $gasUsageTbl = new GasUsage();
                        $ret = $gasUsageTbl->insertRecord([
                            'currency'      => $currency,
                            'tx_id'         => $tx_id,
                            'to_address'    => $record['wallet_address'],
                            'gas_sent'      => $gas_need->__toString(),
                            'gas_used'      => $gas_eth_used->__toString(),
                            'remark'        => 'User-Deposit',
                        ]);
                    }

                    // 2. Send Token
                    $ret = CryptoCurrencyAPI::call_send(
                        $currency,
                        $record['wallet_privkey'],
                        $deposit_wallet['wallet_address'],
                        'MAX',
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
                        Log::channel('crypto')->info("*** Call Send API : code - " . $ret['result'] . " >>");
                        continue;
                    }

                    // 3. Waiting for transaction complete
                    $tx_id = $ret['detail']['hash'];
                    Log::channel('crypto')->info('### Waiting for transaction complete.. TX_ID = ' . $tx_id);
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

                    $gasUsed = $ret['detail']['gasUsed'];
                    print_r("*** Transaction Complete!!! gasUsed = " . $gasUsed . "\n");
                    Log::channel('crypto')->info("*** Transaction Complete!!! gasUsed = " . $gasUsed);

                    $trans_fee = Decimal::create($gasUsed);
                    $trans_fee = $trans_fee->mul($gas_price);
                    $trans_fee = $trans_fee->div(Decimal::create(GAS_UNIT));
                    $trans_fee = $trans_fee->div(Decimal::create(GAS_UNIT));

                    // 4. Create Deposit Record
                    $ret = $depositTbl->insertRecord([
                        'user_id'       => $record['user_id'],
                        'currency'      => $currency,
                        'wallet_addr'   => $record['wallet_address'],
                        'amount'        => $userBalance->__toString(),
                        'deposit_fee'   => $depositFee->__toString(),
                        'transfer_fee'  => $trans_fee->__toString(),
                        'gas_price'     => $gas_price->__toString(),
                        'gas_used'      => $gasUsed,
                        'tx_id'         => $tx_id,
                        'status'        => STATUS_ACCEPTED,
                    ]);
                    if ($ret === false) {
                        continue;
                    }

                    /*$notify_ret = UserNotify::insertRecord([
                        'user_id'   => $record['user_id'],
                        'type'      => USER_NOTIFY_TYPE_DEPOSIT,
                        'currency'  => $currency,
                        'amount'    => $userBalance->__toString(),
                    ]);*/
                } else {
                    // For coin
                    $ret = CryptoCurrencyAPI::call_send(
                        $currency,
                        $record['wallet_privkey'],
                        $deposit_wallet['wallet_address'],
                        'MAX',
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

                    echo("### Deposit to ColdWallet success!,trans_fee:" . $trans_fee->__toString());
                    Log::channel('crypto')->info("### Deposit to ColdWallet success!,trans_fee:" . $trans_fee->__toString() . "detail:" . json_encode($ret['detail']));

                    // 4. Create Deposit Record
                    $tx_id = $ret['detail']['hash'];
                    $ret = $depositTbl->insertRecord([
                        'user_id'       => $record['user_id'],
                        'currency'      => $currency,
                        'wallet_addr'   => $record['wallet_address'],
                        'amount'        => $userBalance->__toString(),
                        'deposit_fee'   => $depositFee->__toString(),
                        'transfer_fee'  => $trans_fee->__toString(),
                        'gas_price'     => 0,
                        'gas_used'      => 0,
                        'type'          => USER_DEPOSIT_TYPE_NORMAL,
                        'tx_id'         => $tx_id,
                        'status'        => STATUS_PENDING,
                    ]);
                    if ($ret === false) {
                        continue;
                    }

                    /*$notify_ret = UserNotify::insertRecord([
                        'user_id'   => $record['user_id'],
                        'type'      => USER_NOTIFY_TYPE_DEPOSIT,
                        'currency'  => $currency,
                        'amount'    => $userBalance->__toString(),
                    ]);*/
                }

                // 5. Increase User Balance
                $ret = TraderBalance::updateBalanceByWhere([
                    'user_id'       => $record['user_id'],
                    'currency'      => $currency,
                ], [
                    'balance'       => $userBalance->__toString(),
                ]);
                Log::channel('crypto')->info(">> Increase User Balance");

                if (empty($ret)) {
                    continue;
                }

                // 6. Add Profit
                $ret = $profitTbl->insertRecord([
                    'user_id'   => $record['user_id'],
                    'currency'  => $currency,
                    'profit'    => $profit->__toString(),
                    'type'      => PROFIT_TYPE_DEPOSIT,
                ]);
                /*$ret = $systemProfitTbl->insertRecord([
                    'user_id'   => $record['user_id'],
                    'currency'  => $currency,
                    'profit'    => $profit->__toString(),
                ], SYSTEM_PROFIT_TYPE_WALLET);*/
                echo("*** Add profit has finished!" . "\n");
                Log::channel('crypto')->info("*** Add profit has finished!");

                echo(">> Finished! >> ***" . "\n");
                Log::channel('crypto')->info(">> Finished! >> ***");
            }
        }

        if ($is_linux) {
            sem_release($semaphore);
        }

        return 0;
    }

    public function checkWalletDeposit($currency, $addr)
    {
        $balanceInfo = CryptoCurrencyAPI::call_get_balance($currency, $addr, COIN_NET);

        if (!isset($balanceInfo['result']) || $balanceInfo['result'] != CryptoCurrencyAPI::_HTTP_RESPONSE_CODE_0) {
            return false;
        }

        $balance = Decimal::create($balanceInfo['detail']);

        return $balance->__toString();
    }
}
