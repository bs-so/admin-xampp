<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\GasUsage;
use Illuminate\Http\Request;

class GasUsageController extends Controller
{
    public function index() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_MANAGER) {
            return redirect()->back();
        }

        $tbl = new GasUsage();
        $total_used = $tbl->getTotalUsed();

        return view('statistics.gas_usage', [
            'total_used'    => $total_used,
        ]);
    }

    public function download() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_MANAGER) {
            return redirect()->back();
        }

        $file = 'ガス使用量.csv';

        $tbl = new GasUsage();
        $ret = $tbl->makeCsv($file);

        return response()->download($file);
    }

    public function ajax_search(Request $request) {
        $params = $request->all();

        $tbl = new GasUsage();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }
}
