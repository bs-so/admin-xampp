<?php

namespace App\Console\Commands;

use DB;
use Litipk\BigNumbers\Decimal;
use App\Modules\CryptoCurrencyAPI;
use Illuminate\Console\Command;

class CheckDisposableWallets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check-disposable-wallets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $table = 'olc_users_disposable';
    protected $table_users = 'olc_users';

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
        $records = DB::table($this->table)
            ->leftJoin($this->table_users, $this->table_users . '.id', '=', $this->table . '.user_id')
            ->select(
                $this->table . '.currency',
                $this->table . '.wallet_address',
                $this->table_users . '.nickname',
                $this->table_users . '.email'
            )
            ->get();

        print_r(">> Check total " . count($records) . " records.\n");
        $count = 0;
        foreach ($records as $index => $record) {
            $currency = $record->currency;
            $address = $record->wallet_address;
            $ret = CryptoCurrencyAPI::call_get_balance($currency, $address);
            if (isset($ret) && !Decimal::create($ret['detail'])->isZero()) {
                $count ++;
                print_r($record->nickname . ", " . $record->email . ", " . $currency . ", " . $address . ", " . $ret['detail'] . "\n");
            }
        }

        print_r("\nTotal Deposits = " . $count . "\n");

        return 0;
    }
}
