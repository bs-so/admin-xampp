<?php

namespace App\Http\Controllers;

use App\Models\Trader;
use App\Models\TraderBalance;
use Auth;
use App\Models\SystemBalance;
use App\Models\SystemTransfer;
use Illuminate\Http\Request;

class SystemTransferController extends Controller
{
    public function index() {
        $user = Auth::user();

        $types = [];
        if ($user->role <= USER_ROLE_ADMIN) {
            $types[] = SYSTEM_BALANCE_TYPE_WALLET;
            $types[] = SYSTEM_BALANCE_TYPE_CASINO_AUTO;
        }
        if ($user->role <= SYSTEM_BALANCE_TYPE_CASINO_MANUAL) {
            $types[] = SYSTEM_BALANCE_TYPE_CASINO_MANUAL;
        }
        if ($user->role <= USER_ROLE_AFFILIATE) {
            $types[] = SYSTEM_BALANCE_TYPE_AFFILIATE;
        }

        return view('system.transfer', [
            'types'     => $types,
        ]);
    }

    public function ajax_search(Request $request) {
        $user = Auth::user();
        $params = $request->all();

        $types = [];
        if ($user->role <= USER_ROLE_ADMIN) {
            $types[] = SYSTEM_BALANCE_TYPE_WALLET;
            $types[] = SYSTEM_BALANCE_TYPE_CASINO_AUTO;
        }
        if ($user->role <= SYSTEM_BALANCE_TYPE_CASINO_MANUAL) {
            $types[] = SYSTEM_BALANCE_TYPE_CASINO_MANUAL;
        }
        if ($user->role <= USER_ROLE_AFFILIATE) {
            $types[] = SYSTEM_BALANCE_TYPE_AFFILIATE;
        }
        $params['types'] = $types;

        $tbl = new SystemTransfer();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function post_casino(Request $request) {
        $user = Auth::user();

        $ret = $this->validate($request, [
            'currency'  => 'required',
            'amount'    => 'required',
        ]);
        $params = $request->all();

        $tbl = new SystemBalance();
        $ret = $tbl->setCasinoBalance($params);

        return redirect()->route('system.transfer')
            ->with('flash_message', 'transfer.system.op_success');
    }

    public function post_affiliate(Request $request) {
        $user = Auth::user();

        $ret = $this->validate($request, [
            'userid'    => 'required',
            'currency'  => 'required',
            'amount'    => 'required',
        ]);
        $params = $request->all();

        $tbl = new SystemBalance();
        $ret = $tbl->transferAffiliateBalance($params);

        if ($ret != 0) {
            $message = '';
            if ($ret == 1) {
                $message = trans('transfer.system.not_enough_balance');
            }
            else {
                $message = trans('transfer.system.op_failed');
            }
            return redirect()->route('system.transfer')
                ->with('error_message', $message);
        }

        return redirect()->route('system.transfer')
            ->with('flash_message', 'transfer.system.op_success');
    }

    public function ajax_getAffiliateBalance(Request $request) {
        $params = $request->all();

        $user_id = Trader::getIDByUserID($params['userid']);

        $tbl = new TraderBalance();
        $ret = $tbl->getUserBalance($user_id, $params['currency']);

        return response()->json($ret);
    }
}
