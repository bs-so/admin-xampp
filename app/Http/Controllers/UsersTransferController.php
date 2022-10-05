<?php

namespace App\Http\Controllers;

use Auth;
use Session;
use App\MailManager;
use App\Models\Trader;
use App\Models\TraderBalance;
use Litipk\BigNumbers\Decimal;
use App\Models\SystemBalance;
use App\Models\TraderTransfer;
use Illuminate\Http\Request;

class UsersTransferController extends Controller
{
    public function index() {
        $user = Auth::user();
        if ($user->role == USER_ROLE_AFFILIATE) {
            return redirect()->back();
        }

        return view('users.transfer');
    }

    public function download(Request $request) {
        $user = Auth::user();
        if ($user->role == USER_ROLE_AFFILIATE) {
            return redirect()->back();
        }

        $type = $request->get('type');
        $user_id = $request->get('trader', 0);

        $file = 'ユーザー送金履歴.csv';
        $tbl = new TraderTransfer();
        $tbl->makeCsv($file, $user_id, $type);

        return response()->download($file);
    }

    public function ajax_search(Request $request) {
        $params = $request->all();

        $tbl = new TraderTransfer();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function post_request(Request $request) {
        $params = $request->all();

        // 1. Validation-1
        $this->validate($request, [
            'receiver'      => 'required',
            'currency'      => 'required',
            'amount'        => 'required',
        ]);
        if ($params['type'] != 1) {
            $this->validate($request, [
                'sender'        => 'required',
            ]);
        }

        $tbl = new Trader();
        $currency = $params['currency'];

        // 2. Get sender info
        $sender = $tbl->getRecordByUserID($params['sender']);
        if (!isset($sender) || !isset($sender->id)) {
            $params['sender_id'] = 0;
        }
        else {
            $params['sender_id'] = $sender->id;
        }

        // 3. Get receiver info
        $receiver = $tbl->getRecordByUserID($params['receiver']);
        if (!isset($receiver) || !isset($receiver->id)) {
            $params['receiver_id'] = 0;
        }
        else {
            $params['receiver_id'] = $receiver->id;
        }

        // 4. Validation-2
        if ($params['receiver_id'] == 0) {
            // Invalid receiver
            return redirect()->route('users.transfer')
                ->with('error_message', trans('transfer.users.invalid_receiver'));
        }

        // 5. Main Process
        $crypto_settings = Session::get('crypto_settings');
        $sender_balance = '0';
        $receiver_balance = '0';
        if ($params['type'] == 1) { // Send from system
            //1. Check system balance
            $systemBalanceTbl = new SystemBalance();
            $balance = $systemBalanceTbl->getBalance(SYSTEM_BALANCE_TYPE_CASINO_MANUAL, $currency);
            if (Decimal::create($balance)->isLessThan(Decimal::create($params['amount']))) {
                // Not enough balance
                return redirect()->route('users.transfer')
                    ->with('error_message', trans('transfer.users.not_enough_balance'));
            }

            // 2. Do Transfer
            $tbl = new TraderTransfer();
            $ret = $tbl->doSystemTransfer($params, $receiver_balance);
            $receiver_balance = _number_format($receiver_balance, min(MINIMUM_BALANCE_DECIMALS, $crypto_settings[$currency]['rate_decimals']));
            if ($ret == true) {
                // Send mail
                $ret = MailManager::send_user_transfer_receiver($receiver, $params, $receiver_balance);
            }
        }
        else { // Send from user
            //1. Check user balance
            $tbl = new TraderBalance();
            $balance = $tbl->getUserBalance($params['sender_id'], $currency);
            if (Decimal::create($balance)->isLessThan(Decimal::create($params['amount']))) {
                // Not enough balance
                return redirect()->route('users.transfer')
                    ->with('error_message', trans('transfer.users.not_enough_balance'));
            }

            // 2. Do Transfer
            $tbl = new TraderTransfer();
            $ret = $tbl->doUserTransfer($params, $sender_balance, $receiver_balance);
            $sender_balance = _number_format($sender_balance, min(MINIMUM_BALANCE_DECIMALS, $crypto_settings[$currency]['rate_decimals']));
            $receiver_balance = _number_format($receiver_balance, min(MINIMUM_BALANCE_DECIMALS, $crypto_settings[$currency]['rate_decimals']));
            if ($ret == true) {
                // Send mail
                $ret = MailManager::send_user_transfer_sender($sender, $params, $sender_balance);
                $ret = MailManager::send_user_transfer_receiver($receiver, $params, $receiver_balance);
            }
        }

        return redirect()->route('users.transfer')
            ->with('flash_message', trans('transfer.users.op_success'));
    }
}
