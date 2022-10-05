<?php

namespace App\Console\Commands;

use DB;
use App\Modules\CryptoCurrencyAPI;
use Litipk\BigNumbers\Decimal;
use Illuminate\Console\Command;

class CheckBTCBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check-btc-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
	protected $table = 'olc_users_disposable';

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
			->where('currency', 'BTC')
			->select('*')
			->get();

		$zero = Decimal::create(0);
		$count = 0;
		print_r(">> Checking total : " . count($records) . "\n");
		foreach ($records as $index => $record) {
			$currency = $record->currency;
			$address = $record->wallet_address;
			$balance = $this->checkWalletDeposit($currency, $address);
			$balance = Decimal::create($balance == '' ? 0 : $balance);
			if ($balance->isGreaterThan($zero)) {
				$count ++;
				print_r($currency . ", " . $address . ", " . $balance->__toString() . "\n");
			}
		}

		print_r("Total remained = " . $count . "\n");

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
