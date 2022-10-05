<?php

namespace App\Console\Commands;

use DB;
use Log;
use App\Models\StaffDeposit;
use App\Models\TraderDeposit;
use App\Modules\CryptoCurrencyAPI;
use Illuminate\Console\Command;

class CheckDepositTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check-deposit-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check deposit transactions';

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
            $semaphore = sem_get(2021021802, 1);
            if (empty($semaphore)) {
                echo 'Failed to get semaphore';
                return;
            }
            if (!sem_acquire($semaphore, true)) {
                echo 'Another command is running already';
                return;
            }
        }

        Log::channel('crypto')->info("---------------------- Check Deposit Transactions ----------------------");

        // Check user deposits
        $records = TraderDeposit::getPendingRecords();
        print_r(">> User Deposit : " . count($records) . " records!" . "\n");
        Log::channel('crypto')->info(">> User Deposit : " . count($records) . " records!");
        foreach ($records as $index => $record) {
            $currency = $record->currency;
            $tx_id = $record->tx_id;

            $ret = CryptoCurrencyAPI::call_checktransaction($currency, $tx_id);
            $result = 'pending';
            if ($ret['result'] == 0) {
                $result = 'finished';
                $ret = TraderDeposit::updateTxStatus($record->id, STATUS_ACCEPTED);
            }

            print_r("    " . $tx_id . " : " . $result . "\n");
            Log::channel('crypto')->info("    " . $tx_id . " : " . $result);
        }

        // Check staff deposits
        $records = StaffDeposit::getPendingRecords();
        print_r(">> Staff Deposit : " . count($records) . " records!" . "\n");
        Log::channel('crypto')->info(">> Staff Deposit : " . count($records) . " records!");
        foreach ($records as $index => $record) {
            $currency = $record->currency;
            $tx_id = $record->tx_id;

            $ret = CryptoCurrencyAPI::call_checktransaction($currency, $tx_id);
            $result = 'pending';
            if ($ret['result'] == 0) {
                $result = 'finished';
                $ret = StaffDeposit::updateTxStatus($record->id, STATUS_ACCEPTED);
            }

            print_r("    " . $tx_id . " : " . $result . "\n");
            Log::channel('crypto')->info("    " . $tx_id . " : " . $result);
        }

        if ($is_linux) {
            sem_release($semaphore);
        }

        return 0;
    }
}
