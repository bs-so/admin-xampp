<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\SystemBalance;
use Illuminate\Http\Request;

class SystemBalanceController extends Controller
{
    public function index() {
        $user = Auth::user();

        $tbl = new SystemBalance();
        $types = [];
        if ($user->role <= USER_ROLE_ADMIN) {
            $types[] = SYSTEM_BALANCE_TYPE_WALLET;
        }
        if ($user->role <= USER_ROLE_CASINO) {
            $types[] = SYSTEM_BALANCE_TYPE_CASINO_MANUAL;
        }
        if ($user->role <= USER_ROLE_AFFILIATE) {
            $types[] = SYSTEM_BALANCE_TYPE_AFFILIATE;
        }

        $balances = $tbl->getAllRecords($types);
        $auto_balances = $tbl->pluckRecords(SYSTEM_BALANCE_TYPE_CASINO_AUTO);

        return view('system.balance', [
            'balances'      => $balances,
            'auto_balances' => $auto_balances,
            'types'         => $types,
        ]);
    }

    public function ajax_getBalance(Request $request) {
        $params = $request->all();

        $tbl = new SystemBalance();
        $ret = $tbl->getBalance($params['type'], $params['currency']);

        return response()->json($ret);
    }
}
