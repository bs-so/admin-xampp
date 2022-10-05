<?php

namespace App\Http\Controllers;

use DB;
use App\Models\CryptoCurrency;
use App\Models\Deposit;
use App\Models\FundHistory;
use App\Models\Trader;
use App\Models\TraderTransfer;
use App\Models\Withdraw;
use App\Models\ServerInfo;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index() {
        $tbl = new Trader();
        $user_count = $tbl->getTotalCount();
        $access_count = $tbl->getAccessCount();

	    $currenciesTbl = new CryptoCurrency();
	    $currencies = $currenciesTbl->getCurrencies();

	    $depositTbl = new Deposit();
	    $user_deposit = $depositTbl->getUserDepositData($currencies);

	    $withdrawTbl = new Withdraw();
	    $user_withdraw = $withdrawTbl->getUserWithdrawData($currencies);

        $result = DB::select(DB::raw('SHOW STATUS WHERE `variable_name` = "Threads_connected";'));
        $connections = $result[0]->Value;

        return view('home', [
	        'user_count'        => $user_count,
		    'access_count'      => $access_count,
		    'connections'       => $connections,
		    'currencies'        => $currencies,
		    'user_deposit'      => $user_deposit,
		    'user_withdraw'     => $user_withdraw,
	    ]);
    }

    public function ajax_getRegisterData(Request $request) {
        $tbl = new Trader();
        $ret = $tbl->getRegisterData(10);

        return response()->json($ret);
    }

	public function ajax_getUserTransferData(Request $request) {
		$currenciesTbl = new CryptoCurrency();
		$currencies = $currenciesTbl->getCurrencies();

		$depositTbl = new Deposit();
		$user_deposit = $depositTbl->getUserDepositData($currencies);

		$withdrawTbl = new Withdraw();
		$user_withdraw = $withdrawTbl->getUserWithdrawData($currencies);

		$transferTbl = new TraderTransfer();
		$user_transfer = $transferTbl->getUserTransferData($currencies);
		$system_transfer = $transferTbl->getSystemTransferData($currencies);

		$ret['currency'] = $currencies;
		$ret['user_deposit'] = $user_deposit;
		$ret['user_withdraw'] = $user_withdraw;
		$ret['user_transfer'] = $user_transfer;
		$ret['system_transfer'] = $system_transfer;

		return response()->json($ret);
	}

	public function ajax_getUserWithdrawData(Request $request) {
		$currenciesTbl = new CryptoCurrency();
		$currencies = $currenciesTbl->getCurrencies();

		$tbl = new Withdraw();
		$ret = $tbl->getUserWithdrawData($currencies);

		return response()->json($ret);
	}

	public function ajax_getManagerDepositData(Request $request) {
		$currenciesTbl = new CryptoCurrency();
		$currencies = $currenciesTbl->getCurrencies();

		$tbl = new FundHistory();
		$ret = $tbl->getManagerDepositData($currencies);

		return response()->json($ret);
	}

	public function ajax_getManagerWithdrawData(Request $request) {
		$currenciesTbl = new CryptoCurrency();
		$currencies = $currenciesTbl->getCurrencies();

		$tbl = new FundHistory();
		$ret = $tbl->getManagerWithdrawData($currencies);

		return response()->json($ret);
	}

	public function ajax_getServerInfo(Request $request) {
        $tbl = new ServerInfo();

        $total = $tbl->getInfo(SERVER_INFO_TOTAL_RAM);
        $free = $tbl->getInfo(SERVER_INFO_FREE_RAM);
        $percent = ($total == 0 ? 0 : $free * 100 / $total);

        return response()->json(array(
            'total'     => $total,
            'free'      => $free,
            'percent'   => $percent,
        ));
    }

    public function ajax_getTransferFees(Request $request) {
        $ret = array(
            'BTC(satoshi/byte)'     => g_getBTCFees(),
            'ETH(gwei)'             => g_getGasPrices(),
        );

        return response()->json($ret);
    }
}
