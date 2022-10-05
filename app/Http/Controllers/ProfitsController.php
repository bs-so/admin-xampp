<?php

namespace App\Http\Controllers;

use App\Models\Profits;
use Auth;
use Session;
use App\Models\SystemProfit;
use Illuminate\Http\Request;

class ProfitsController extends Controller
{
    public function casino(Request $request) {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_MANAGER) {
            return redirect()->back();
        }

        $tbl = new SystemProfit();
        $total_data = $tbl->getTotalData(SYSTEM_PROFIT_TYPE_CASINO);

        return view('statistics.profits', [
            'type'              => SYSTEM_PROFIT_TYPE_CASINO,
            'total_data'        => $total_data,
        ]);
    }

    public function wallet(Request $request) {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN) {
            return redirect()->back();
        }

        $tbl = new Profits();
        $total_data = $tbl->getTotalData();

        return view('statistics.profits', [
            'type'              => SYSTEM_PROFIT_TYPE_WALLET,
            'total_data'        => $total_data,
        ]);
    }

    public function detail(Request $request) {
        $sel_date = $request->get('date');
        $sel_currency = $request->get('currency');
        $sel_type = $request->get('type');

        $user = Auth::user();
        if ($user->role == USER_ROLE_AFFILIATE || ($sel_type == SYSTEM_PROFIT_TYPE_WALLET && $user->role != USER_ROLE_ADMIN)) {
            return redirect()->back();
        }

        if ($sel_type == SYSTEM_PROFIT_TYPE_WALLET) {
            $tbl = new Profits();
            $month_data = $tbl->getMonthData($sel_date, $sel_currency);
        }
        else {
            $tbl = new SystemProfit();
            $month_data = $tbl->getMonthData($sel_date, $sel_currency, $sel_type);
        }

        return view('statistics.profits_detail', [
            'sel_date'          => $sel_date,
            'sel_currency'      => $sel_currency,
            'sel_type'          => $sel_type,
            'month_data'        => $month_data,
        ]);
    }

    public function all(Request $request) {
        $sel_date = $request->get('date');
        $sel_currency = $request->get('currency');
        $sel_type = $request->get('type');

        $user = Auth::user();
        if ($user->role == USER_ROLE_AFFILIATE || ($sel_type == SYSTEM_PROFIT_TYPE_WALLET && $user->role != USER_ROLE_ADMIN)) {
            return redirect()->back();
        }

        return view('statistics.profits_all', [
            'sel_date'          => $sel_date,
            'sel_currency'      => $sel_currency,
            'sel_type'          => $sel_type,
        ]);
    }

    public function ajax_search(Request $request) {
        $params = $request->all();
        $sel_type = $request->get('type');

        $user = Auth::user();
        if ($user->role == USER_ROLE_AFFILIATE || ($sel_type == SYSTEM_PROFIT_TYPE_WALLET && $user->role != USER_ROLE_ADMIN)) {
            return redirect()->back();
        }

        if ($sel_type == SYSTEM_PROFIT_TYPE_WALLET) {
            $tbl = new Profits();
            $ret = $tbl->getForDatatable($params);
        }
        else {
            $tbl = new SystemProfit();
            $ret = $tbl->getForDatatable($params);
        }

        return response()->json($ret);
    }
}
