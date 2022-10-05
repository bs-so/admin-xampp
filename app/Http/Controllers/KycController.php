<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Identity;
use Illuminate\Http\Request;

class KycController extends Controller
{
    public function index() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        return view('requests.kyc');
    }

    public function download(Request $request) {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        $id = $request->get('id');

        $tbl = new Identity();
        $ret = $tbl->getPhotoUrl($id);

        if ($ret === false || $ret == '') {
            return redirect()->back();
        }

        $file = $ret;
        $arr = explode('/', $ret);
        $dest = 'uploads/temp/' . $arr[count($arr) - 1];
        copy($file, $dest);

        return response()->download($dest);
    }

    public function csv() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        $file = 'KYC申請一覧.csv';

        $tbl = new Identity();
        $ret = $tbl->makeCsv($file);

        return response()->download($file);
    }

    public function ajax_search(Request $request) {
        $params = $request->all();

        $tbl = new Identity();
        $ret = $tbl->getUserList($params);

        return response()->json($ret);
    }

    public function ajax_updateStatus(Request $request) {
        $params = $request->all();

        $tbl = new Identity();
        $ret = $tbl->updateStatus($params);

        return response()->json($ret);
    }

    public function ajax_getIdentityList(Request $request) {
        $params = $request->all();

        $tbl = new Identity();
        $ret = $tbl->getIdentityList($params);

        return response()->json($ret);
    }
}
