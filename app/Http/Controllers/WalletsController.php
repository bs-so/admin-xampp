<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\ColdWallets;
use Illuminate\Http\Request;

class WalletsController extends Controller
{
    public function index(Request $request) {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN) {
            return redirect()->back();
        }

        $sel_currency = $request->get('currency', 'BTC');

        return view('wallets.index', [
            'sel_currency'      => $sel_currency,
        ]);
    }

    public function balance(Request $request) {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN) {
            return redirect()->back();
        }

        $sel_currency = $request->get('currency', 'BTC');

        $tbl = new ColdWallets();
        $summary_data = $tbl->getBalanceSummary($sel_currency);

        return view('wallets.balance', [
            'sel_currency'      => $sel_currency,
            'summary_data'      => $summary_data,
        ]);
    }

    public function balance_detail(Request $request) {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN) {
            return redirect()->back();
        }

        $sel_currency = $request->get('currency', 'BTC');
        $sel_type = $request->get('type', WALLET_TYPE_COLD);

        return view('wallets.balance_detail', [
            'sel_currency'      => $sel_currency,
            'sel_type'          => $sel_type,
        ]);
    }

    public function ajax_search(Request $request) {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN) {
            return redirect()->back();
        }

        $params = $request->all();

        $tbl = new ColdWallets();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_checkAddress(Request $request) {
        $params = $request->all();

        $tbl = new ColdWallets();
        $ret = $tbl->checkAddress($params);

        return response()->json($ret);
    }

    public function ajax_addWallet(Request $request) {
        $params = $request->all();

        $tbl = new ColdWallets();
        $ret = $tbl->addWallet($params);

        return response()->json($ret);
    }

    public function ajax_setPrivateKey(Request $request) {
        $params = $request->all();

        $tbl = new ColdWallets();
        $ret = $tbl->setPrivateKey($params);

        return response()->json($ret);
    }

    public function ajax_delete(Request $request) {
        $id = $request->get('id');

        $tbl = new ColdWallets();
        $ret = $tbl->deleteRecord($id);

        return response()->json($ret);
    }

    public function ajax_getWalletList(Request $request) {
        $params = $request->all();

        $tbl = new ColdWallets();
        $ret = $tbl->getWalletList($params);

        return response()->json($ret);
    }

    public function ajax_specify(Request $request) {
        $params = $request->all();

        $tbl = new ColdWallets();
        $ret = $tbl->specifyWallet($params);

        return response()->json($ret);
    }

    public function ajax_getBalanceSummary(Request $request) {
        $currency = $request->get('currency');

        $tbl = new ColdWallets();
        $ret = $tbl->getBalanceSummary($currency);

        return response()->json($ret);
    }

    public function ajax_refreshBalance(Request $request ){
        $params = $request->all();

        $tbl = new ColdWallets();
        $ret = $tbl->refreshBalance($params);

        return response()->json($ret);
    }

    public function ajax_getTotalBalance(Request $request) {
        $params = $request->all();

        $tbl = new ColdWallets();
        $ret = $tbl->refreshBalance($params);
        $ret = $tbl->getTotalBalance($params['currency']);

        return response()->json($ret);
    }
}
