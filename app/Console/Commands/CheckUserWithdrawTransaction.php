<?php

namespace App\Console\Commands;

use Log;
use App\Modules\CryptoCurrencyAPI;
use App\Models\TraderWithdraw;
use Illuminate\Console\Command;

class CheckUserWithdrawTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check-user-withdraw-transaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
            $semaphore = sem_get(2021030202, 1);
            if (empty($semaphore)) {
                echo 'Failed to get semaphore';
                return 0;
            }
            if (!sem_acquire($semaphore, true)) {
                echo 'Another command is running already';
                return 0;
            }
        }

        Log::channel('crypto')->info("-------------------------- Check user withdraw transaction --------------------------");

        $txList = TraderWithdraw::whereIn('status', [STATUS_PENDING])
            ->where('tx_id', '!=', '')
            ->whereNotIn('currency', ['ETH', 'USDT'])
            ->get()->toArray();

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
                continue;
            }
            if ($ret === false) {
                print_r("**** Pending\n");
                Log::info("**** Pending");
                continue;
            }

            $ret = TraderWithdraw::updateRecords(['id' => $id], ['status' => STATUS_ACCEPTED]);
            print_r("**** Finished\n");
            Log::info("**** Finished");
        }

        if ($is_linux) {
            sem_release($semaphore);
        }

        return 0;
    }
}
