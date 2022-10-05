<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Transactions;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN) {
            return redirect()->back();
        }

        return view('transactions.list');
    }

    public function download() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN) {
            return redirect()->back();
        }

        $file = 'トランザクション一覧.csv';

        $tbl = new Transactions();
        $ret = $tbl->makeCsv($file);

        return response()->download($file);
    }

    public function ajax_search(Request $request) {
        $params = $request->all();

        $tbl = new Transactions();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }
}
