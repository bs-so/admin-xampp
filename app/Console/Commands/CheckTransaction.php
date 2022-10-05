<?php

namespace App\Console\Commands;

use Log;
use Litipk\BigNumbers\Decimal;
use App\Models\Transactions;
use App\Modules\CryptoCurrencyAPI;
use Illuminate\Console\Command;

class CheckTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check-transaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check transactions';

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
            $semaphore = sem_get(2021030201, 1);
            if (empty($semaphore)) {
                echo 'Failed to get semaphore';
                return 0;
            }
            if (!sem_acquire($semaphore, true)) {
                echo 'Another command is running already';
                return 0;
            }
        }

        Log::channel('crypto')->info("-------------------------- Check coldwallet transaction --------------------------");

        $txList = Transactions::whereIn('status', [TRANSFER_STATUS_SENT, TRANSFER_STATUS_PENDING])->get()->toArray();
        $tbl = new Transactions();

        foreach ($txList as $tx)
        {
            $id = $tx['id'];
            $tx_id = $tx['tx_id'];
            $currency = $tx['currency'];

            print_r(">> Checking Transaction, id:" . $tx['id'] . "," . $currency . "\n");
            Log::info("");
            Log::info(">> Checking Transaction, id:" . $tx['id'] . "," . $currency);

            $gas = 0;
            $ret = CryptoCurrencyAPI::CheckTransaction($tx_id, $currency, '', '', $gas);
            if ($ret === STATUS_FAILED) {
                print_r("**** Failed\n");
                Log::info("**** Failed");
                $ret = Transactions::updateRecord(['id' => $tx['id']], ['status' => TRANSFER_STATUS_FAILED]);
                continue;
            }
            if ($ret === false) {
                print_r("**** Pending\n");
                Log::info("**** Pending");
                continue;
            }

            if ($currency == 'BTC') {
                $ret = Transactions::updateRecord(['id' => $tx['id']], ['status' => TRANSFER_STATUS_FINISHED]);
            }
            else if ($currency == MAIN_CURRENCY || $currency == 'ETH' || $currency == 'USDT') {
                $gas_used = Decimal::create($tx['transfer_fee']);
                $gas_used = $gas_used->mul(Decimal::create($gas));
                $gas_used = $gas_used->div(Decimal::create(GAS_UNIT));
                $gas_used = $gas_used->div(Decimal::create(GAS_UNIT));
                $ret = Transactions::updateRecord(
                    ['id' => $tx['id']],
                    [
                        'status'        => TRANSFER_STATUS_FINISHED,
                        'transfer_fee'  => $gas_used->__toString(),
                    ]
                );
            }

            print_r("**** Finished\n");
            Log::info("**** Finished");
        }

        if ($is_linux) {
            sem_release($semaphore);
        }

        return 0;
    }
}
