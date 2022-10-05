<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\StaffWithdrawQueue;
use App\Models\ColdWallets;
use App\Modules\CryptoCurrencyAPI;
use App\Models\StaffWithdraw;
use Litipk\BigNumbers\Decimal;

class WithdrawController extends Controller
{
    public function index() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN) {
            return redirect()->back();
        }

        return view('coldwallet.withdraw', [
        ]);
    }

    public function ajax_queue(Request $request) {
        $params = $request->all();

        $tbl = new StaffWithdrawQueue();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_request(Request $request) {
        $this->validate($request, [
            'currency'      => 'required',
            'amount'        => 'required',
            'address'       => 'required',
            'remark'        => 'required',
        ]);

        $params = $request->all();

        // Check Balance
        $tbl = new ColdWallets();
        $balance = Decimal::create($tbl->getWithdrawBalance($params['currency']));
        $requested = Decimal::create($params['amount']);

        /*if ($balance->isLessThan($requested)) {
            return response()->json(1);
        }*/

        // Check Address
        $main_currency = $params['currency'];
        if ($params['currency'] == 'USDT') {
            $main_currency = 'ETH';
        }
        $balanceInfo = CryptoCurrencyAPI::call_get_balance($main_currency, $params['address'],COIN_NET);
        if (!isset($balanceInfo['result']) || !isset($balanceInfo['detail']) || $balanceInfo['result'] != CryptoCurrencyAPI::_HTTP_RESPONSE_CODE_0) {
            return response()->json(2);
        }
        $withdrawWallet = ColdWallets::getWithdrawWallet($params['currency']);

        $user = Auth::user();
        $params['staff_id'] = $user->id;
        $params['cold_wallet_id'] = $withdrawWallet['id'];

        $tbl = new StaffWithdraw();
        $newId = $tbl->insertRecord($params);

        $tbl = new StaffWithdrawQueue();
        $ret = $tbl->insertRecord($params, $newId);

        return response()->json(0);
    }

    public function ajax_getWalletBalances(Request $request) {
        $currency = $request->get('currency');

        $tbl = new StaffWithdraw();
        $ret = $tbl->getWalletBalances($currency);

        return response()->json($ret);
    }
}
