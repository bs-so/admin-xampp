<?php

namespace App\Console\Commands;

use Log;
use App\Models\Trader;
use App\Models\SystemBalance;
use App\Models\TraderDeposit;
use App\Models\TraderBalance;
use Litipk\BigNumbers\Decimal;
use Illuminate\Console\Command;

class InputCasinoTradeLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:input-casino-trade-log {file}';

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
        $file = $this->argument('file');
        print_r(">> InputCasinTradeLog has started!!! File = " . $file . "\n");

        $contents = file_get_contents($file);
        $lines = explode("\n", $contents);
        $line_count = count($lines);
        print_r("    Line Count = " . $line_count . "\n");

        $user_id = '';
        $success = 0;
        $failed = 0;
        $total_profit = array();
        $tbl = new SystemBalance();
        $traderTbl = new Trader();

        $btc_unit = Decimal::create(100000000);
        $eth_unit = Decimal::create(1000000000);

        for ($i = 1; $i < $line_count; $i ++) {
            $items = explode(",", $lines[$i]);
            if (!isset($items[12])) break;

            $win = $items[1];
            $userid = $items[4];
            $currency = $items[5];
            $amount = $items[12];
            $user_id = $traderTbl->getTraderIdByUserID($userid);

            $amount = Decimal::create($amount);
            if ($win == 'win') {
                // System's loss
                $amount = $amount->mul(Decimal::create(-1));
            }
            if ($currency == 'BTC') {
                $amount = $amount->div($btc_unit);
            }
            else if ($currency == 'ETH') {
                $amount = $amount->div($eth_unit)->div($eth_unit);
            }

            print_r("    #" . $i . ": " . $userid . "(" . $user_id . "), " . $currency . ", " . $amount . ", " . $win);

            $ret = $tbl->insertCasinoLog($user_id, SYSTEM_BALANCE_TYPE_CASINO_AUTO, $currency, $amount->__toString());
            if ($ret == true) {
                $success ++;
                if (!isset($total_profit[$currency])) {
                    $total_profit[$currency] = Decimal::create(0);
                }
                $total_profit[$currency] = $total_profit[$currency]->add($amount);
                print_r(" - OK" . "\n");
            }
            else {
                $failed ++;
                print_r(" - Failed" . "\n");
            }
        }

        print_r("\n");
        print_r(">> Total = " . $line_count . ", Success = " . $success . ", Failed = " . $failed . "\n");
        print_r("------------------------------------------------------------------------\n");
        $balanceTbl = new TraderBalance();
        foreach ($total_profit as $currency => $profit) {
            $total_deposit = TraderDeposit::getUserTotalDeposit($user_id, $currency);
            $balance = $balanceTbl->getUserBalance($user_id, $currency);

            $diff = Decimal::create($total_deposit);
            $diff = $diff->sub(Decimal::create($balance));

            print_r("    " . $currency . ", " . $profit->__toString() . ", " . $total_deposit . ", " . $balance);
            if ($diff->sub(Decimal::create($profit))->isZero()) {
                print_r(" - OK" . "\n");
            }
            else {
                print_r(" - Failed" . "\n");
            }
        }

        return 0;
    }
}
