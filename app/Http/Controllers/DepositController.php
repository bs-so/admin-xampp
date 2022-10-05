<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\StaffDisposable;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DepositController extends Controller
{
    protected $marker = '/public/app-assets/images/qrmarker.png';

    public function index() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN) {
            return redirect()->back();
        }

        $tbl = new StaffDisposable();
        $ret = $tbl->checkWallets($user->id);

        return view('coldwallet.deposit', [
        ]);
    }

    public function ajax_wallets(Request $request) {
        $user = Auth::user();
        $params = $request->all();
        $params['staff_id'] = $user->id;

        $tbl = new StaffDisposable();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_getQRCode(Request $request) {
        $id = $request->get('id');

        $tbl = new StaffDisposable();
        $record = $tbl->getRecordById($id);

        $qr_code = base64_encode(QrCode::format('png')->size(200)->merge($this->marker, .3)->encoding('UTF-8')->errorCorrection('H')->generate($record->wallet_address));
        $result = array(
            'currency'  => $record->currency,
            'address'   => $record->wallet_address,
            'qr_code'   => $qr_code,
        );

        return response()->json($result);
    }
}
