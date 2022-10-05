<?php

namespace App\Http\Controllers;

use App\Models\Banks;
use App\Models\CasinoApi;
use Auth;
use App\Models\Trader;
use App\Models\TraderBalance;
use App\Models\TraderDeposit;
use App\Models\TraderWithdraw;
use App\Models\TraderExchange;
use App\Models\TraderWithdrawCash;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        return view('users.list', [
        ]);
    }

    public function detail(Request $request)
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        $id = $request->get('id');

        $tbl = new Trader();
        $trader = $tbl->getRecordById($id);

        $tbl = new TraderBalance();
        $balance = $tbl->getDataById($id);

        return view('users.detail', [
            'trader'    => $trader,
            'balance'   => $balance,
        ]);
    }

    public function deposit_list()
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        return view('history.history-users-deposit', [
        ]);
    }

    public function withdraw_list()
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        return view('history.history-users-withdraw', [
        ]);
    }

    public function exchange_list()
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        return view('history.history-users-exchange', [
        ]);
    }

    public function transfer_list()
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        return view('history.history-users-transfer', [
        ]);
    }

    public function withdraw_cash_list() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        return view('history.history-users-withdraw-cash', [
        ]);
    }

    public function download(Request $request)
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        $params = $request->all();

        $name = '全て';
        $trader = 0;
        if (isset($params['trader'])) {
            $tbl = new Trader();
            $record = $tbl->getRecordById($params['trader']);
            $name = $record->name;
            $trader = $params['trader'];
        }

        switch ($params['type']) {
            case 0:
                $file = '入金履歴（' . $name . '）.csv';
                $tbl = new TraderDeposit();
                $ret = $tbl->makeCsv($file, $trader);
                return response()->download($file);
            case 1:
                $file = '交換履歴（' . $name . '）.csv';
                $tbl = new TraderExchange();
                $ret = $tbl->makeCsv($file, $trader);
                return response()->download($file);
            case 2:
                $file = '出金履歴-暗号通貨（' . $name . '）.csv';
                $tbl = new TraderWithdraw();
                $ret = $tbl->makeCsv($file, $trader);
                return response()->download($file);
            case 3:
                $file = '出金履歴-現金（' . $name . '）.csv';
                $tbl = new TraderWithdrawCash();
                $ret = $tbl->makeCsv($file, $trader);
                return response()->download($file);
        }

        return 0;
    }

    public function ajax_search(Request $request)
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        $params = $request->all();
        $tbl = new Trader();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_getBalance(Request $request) {
        $params = $request->all();

        $tbl = new Trader();
        $params['user_id'] = $tbl->getTraderIdByUserID($params['sender']);

        $tbl = new TraderBalance();
        $ret = $tbl->getUserBalance($params['user_id'], $params['currency']);

        return response()->json($ret);
    }

    public function ajax_updateStatus(Request $request) {
        $params = $request->all();

        $tbl = new Trader();
        $ret = $tbl->updateStatus($params);

        return response()->json($ret);
    }

    public function ajax_deposit_search(Request $request)
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        $params = $request->all();
        $tbl = new TraderDeposit();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);

    }

    public function ajax_withdraw_search(Request $request)
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        $params = $request->all();
        $tbl = new TraderWithdraw();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_exchange_search(Request $request)
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        $params = $request->all();
        $tbl = new TraderExchange();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_withdraw_cash_search(Request $request)
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        $params = $request->all();
        $tbl = new TraderWithdrawCash();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_register(Request $request) {
        $id = $request->get('id');

        $tbl = new Trader();
        $user = $tbl->getRecordById($id);

        $result = CasinoApi::register((array)$user);
        if ($result == CASINO_REGISTER_SUCCESS) {
            $ret = $tbl->updateStatus(array(
                'id'        => $user->id,
                'status'    => STATUS_ACTIVE,
            ));
            $ret = TraderBalance::addDefaultBalance($user->id);
        }

        return response()->json($result);
    }

    public function ajax_delete(Request $request) {
        $user_id = $request->get('id');
        $tbl = new Trader();

        $ret = $tbl->deleteRecordById($user_id);
        return $ret;
    }
}
