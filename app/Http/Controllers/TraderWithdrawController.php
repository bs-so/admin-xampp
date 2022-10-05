<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Banks;
use App\Models\TraderWithdrawCash;
use App\Models\Master;
use App\Models\Profits;
use App\Models\TraderBalance;
use Illuminate\Http\Request;
use App\Models\TraderWithdraw;

class TraderWithdrawController extends Controller
{
    public function index() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        return view('requests.withdraw-outline');
    }

    public function withdraw_cash() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        $withdrawFee = Master::getValue(WITHDRAW_FEE);

        return view('requests.withdraw-cash', [
            'withdrawFee'   => $withdrawFee,
        ]);
    }

    public function withdrawRequestOutline(Request $request)
    {
        $cryptoWithdraw = new TraderWithdraw();
        $result = $cryptoWithdraw->getWithdrawOutLine($request->all());

        return response()->json($result);
    }

    public function withdrawRequestList(Request $request)
    {
        $id = $request->get('id');

        return view('requests.withdraw-list', [
            'currency' => $id,
        ]);
    }

    public function withdrawRequestListData(Request $request)
    {
        $cryptoWithdraw = new TraderWithdraw();
        $result = $cryptoWithdraw->getWithdrawList($request->all());

        return response()->json($result);
    }

    public function withdrawComplete(Request $request)
    {
        $params = $request->all();

        $cryptoWithdraw = new TraderWithdraw();
        $result = $cryptoWithdraw->addWithdrawQueue($params);

        return response()->json($result);
    }

    public function withdrawCancel(Request $request)
    {
        $params = $request->all();

        $tbl = new TraderBalance();
        $ret = $tbl->rollbackBalances($params);

        $cryptoWithdraw = new TraderWithdraw();
        $result = $cryptoWithdraw->cancelRequests($params);

        return response()->json($result);
    }

    public function ajax_cashList(Request $request) {
        $params = $request->all();

        $tbl = new TraderWithdrawCash();
        $ret = $tbl->getRequestList($params);

        return response()->json($ret);
    }

    public function ajax_cashApprove(Request $request) {
        $params = $request->all();

        $tbl = new TraderWithdrawCash();
        $ret = $tbl->approveRequests($params);

        return response()->json($ret);
    }

    public function ajax_cashCancel(Request $request) {
        $params = $request->all();

        $tbl = new TraderWithdrawCash();
        $ret = $tbl->cancelRequests($params);

        return response()->json($ret);
    }
}
