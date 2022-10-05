<?php

namespace App\Http\Controllers;

use App\Models\AffiliateTransfer;
use Auth;
use App\Models\SystemBalance;
use Illuminate\Http\Request;

class AffiliateTransferController extends Controller
{
    public function deposit() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN) {
            return redirect()->back();
        }

        $tbl = new SystemBalance();
        $balances = $tbl->getAll();

        return view('affiliate.deposit', [
            'balances'  => $balances,
        ]);
    }

    public function deposit_sumbit(Request $request) {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN) {
            return redirect()->back();
        }

        $ret = $this->validate($request, [
            'currency'  => 'required',
            'amount'    => 'required',
        ]);
        $params = $request->all();

        $tbl = new SystemBalance();
        $ret = $tbl->updateBalance(SYSTEM_BALANCE_AFFILIATE, $params);

        return redirect()->route('affiliate.deposit')
            ->with('flash_message', 'affiliate.message.deposit_success');
    }

    public function transfer() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN) {
            return redirect()->back();
        }

        return view('affiliate.transfer');
    }

    public function download() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN) {
            return redirect()->back();
        }

        $file = 'アフィ入出金一覧.csv';

        $tbl = new AffiliateTransfer();
        $ret = $tbl->makeCsv($file);

        return response()->download($file);
    }

    public function ajax_search(Request $request) {
        $params = $request->all();

        $tbl = new AffiliateTransfer();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }
}
