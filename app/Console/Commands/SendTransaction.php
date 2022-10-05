<?php

namespace App\Console\Commands;

use DB;
use Log;
use Session;
use Litipk\BigNumbers\Decimal;
use App\Models\Master;
use App\Models\GasUsage;
use App\Models\CryptoSettings;
use App\Models\Transactions;
use App\Models\ColdWallets;
use App\Modules\CryptoCurrencyAPI;
use Illuminate\Console\Command;

class SendTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:send-transaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send transactions';

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
            $semaphore = sem_get(2021020901, 1);
            if (empty($semaphore)) {
                echo 'Failed to get semaphore';
                return;
            }
            if (!sem_acquire($semaphore, true)) {
                echo 'Another command is running already';
                return;
            }
        }

        $transactionList = Transactions::where(['status' => TRANSFER_STATUS_PENDING])->get()->toArray();
        $tbl = new Transactions();

        $cryptoSettings = Session::get('crypto_settings');
        if (!isset($cryptoSettings) || count($cryptoSettings) == 0) {
            $cryptoSettings = CryptoSettings::getAll();
            Session::put('crypto_settings');
        }
        $gastank = ColdWallets::getGasTankWallet();
        $gasPriceMode = Master::getValue('GAS_PRICE_MODE');

        foreach ($transactionList as $transaction)
        {
            $currency = $transaction['currency'];

            print_r(">> Sending Transaction, id:" . $transaction['id'] . "," . $currency . "\n");
            Log::channel('crypto')->info("");
            Log::channel('crypto')->info(">> Sending Transaction, id:" . $transaction['id'] . "," . $currency);

            $id = $transaction['id'];
            $currency = $transaction['currency'];
            $tx_signed = $transaction['signed_hash'];

            $from_address = $transaction['from_address'];
            $amount = Decimal::create($transaction['amount']);

            $_trans_fee = Decimal::create(0);

            if ($currency == 'ETH' || $currency == 'USDT') {
                if (!isset($gastank) || empty($gastank)) {
                    print_r("     No Gastank!!! Please create it!" . "\n");
                    Log::error("     No Gastank!!! Please create it!");
                    continue;
                }

                // 3.1 Check ETH balance
                $gasInfo = CryptoCurrencyAPI::call_get_balance('ETH', $from_address, COIN_NET);
                $gas_balance = Decimal::create((isset($gasInfo['detail'])) ? $gasInfo['detail'] : '0');

                if ($currency == 'ETH') {
                    $gas_balance = $gas_balance->sub($amount);
                }

                print_r("   Now exist balance: " . $gas_balance->__toString() . "\n");
                Log::channel('crypto')->info("   Now exist balance: " . $gas_balance->__toString());

                $gas_price = Decimal::create($cryptoSettings[$currency]['gas_price']);
                $gas_limit = Decimal::create($cryptoSettings[$currency]['gas_limit']);
                $gas_need = Decimal::create($cryptoSettings[$currency]['gas']);
                $eth_gas_price = $cryptoSettings['ETH']['gas_price'];
                $gwei = Decimal::create('1000000000');

                if ($transaction['gas_price'] > 0) {
                    $gas_price = Decimal::create($transaction['gas_price']);
                    $gas_limit = Decimal::create($transaction['gas_limit']);
                    $gas_need = $gas_price->mul($gas_limit);
                    $gas_need = $gas_need->div($gwei)->div($gwei);
                    $eth_gas_price = $gas_price->__toString();
                }

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
                print_r("     Applying GasPrice : " . $gas_price->__toString() . ", Gas : " . $gas_need->__toString() . "\n");
                Log::channel('crypto')->info("     Applying GasPrice : " . $gas_price->__toString() . ", Gas : " . $gas_need->__toString());

                if ($gas_balance->isLessThan($gas_need)) {
                    // 3.2 Require gas from Gastank
                    print_r("Require gas from Gastank!!!". "\n");
                    Log::channel('crypto')->info("Require gas from Gastank!!!");

                    $gastank_wallet = ColdWallets::getGasTankWallet();
                    $ret = CryptoCurrencyAPI::call_send(
                        'ETH',
                        $gastank_wallet['wallet_privkey'],
                        $from_address,
                        $gas_need->__toString(),
                        '',
                        COIN_NET,
                        $cryptoSettings['ETH']['gas_price'],
                        $cryptoSettings['ETH']['gas_limit']
                    );

                    if (!isset($ret['result'])) {
                        Log::channel('crypto')->error("#### Call Send API failed. >>");
                        continue;
                    }
                    if ($ret['result'] != 0) {
                        Log::channel('crypto')->error("#### Call Send API : code - " . $ret['result'] . " >>");
                        continue;
                    }

                    $tx_id = $ret['detail']['hash'];
                    Log::channel('crypto')->info("     Sent gas request to GasTank. TX_ID = " . $tx_id);

                    // 3.3 Waiting for transaction complete
                    Log::channel('crypto')->info('     Waiting for gas receive..');
                    while (true) {
                        $ret = CryptoCurrencyAPI::call_checktransaction('ETH', $tx_id);

                        if (!isset($ret['result'])) {
                            Log::error("#### Call CheckTransaction API failed. >>");
                            sleep(1);
                            continue;
                        }
                        if ($ret['result'] != 0) {
                            Log::error("#### Call CheckTransaction API : code - " . $ret['result'] . " >>");
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
                        'remark'        => 'Send-ColdWallet-TX',
                    ]);
                }

                $_trans_fee = Decimal::create($gas_price);
            }
            else {
                $amount = Decimal::create($transaction['amount']);
                $fee = Decimal::create($cryptoSettings[$currency]['transfer_fee']);
                $total_amount = Decimal::create($amount);
                $total_amount = $total_amount->add($fee);

                $_trans_fee = Decimal::create($fee);
            }

            print_r("   Sending transactions..." . "\n");
            Log::channel('crypto')->info("   Sending transactions...");
            $tx_id = CryptoCurrencyAPI::SendTransaction($currency, $tx_signed);
            if ($tx_id === false) {
                print_r("**** Failed\n");
                Log::critical("**** Failed");
                $ret = Transactions::updateRecord(['id' => $id], [
                    'status'        => TRANSFER_STATUS_FAILED,
                ]);
                continue;
            }

            Log::channel('crypto')->info("#### Call API Success, waiting for confirm...");
            print_r("  * TX_ID: " . $tx_id . "\n");
            Log::channel('crypto')->info("#### TX_ID : " . $tx_id);

            Transactions::updateRecord(['id' => $id], [
                'status'        => TRANSFER_STATUS_SENT,
                'tx_id'         => $tx_id,
                'transfer_fee'  => $_trans_fee->__toString(),
            ]);

            print_r("**** Success\n");
            Log::channel('crypto')->info("**** Success");
        }

        if ($is_linux) {
            sem_release($semaphore);
        }

        return 0;
    }
}
