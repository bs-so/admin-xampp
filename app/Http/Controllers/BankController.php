<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Banks;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function ajax_search(Request $request) {
        $params = $request->all();

        $tbl = new Banks();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_getInfo(Request $request) {
        $id = $request->get('id');

        $tbl = new Banks();
        $ret = $tbl->getRecordById($id);

        return response()->json($ret);
    }

    public function ajax_add(Request $request) {
        $this->validate($request, [
            'name'      => 'required',
            'status'    => 'required',
        ]);

        $params = $request->all();

        $tbl = new Banks();
        $ret = $tbl->insertRecord($params);

        return response()->json($ret);
    }

    public function ajax_edit(Request $request) {
        $this->validate($request, [
            'name'      => 'required',
            'status'    => 'required',
        ]);

        $params = $request->all();

        $tbl = new Banks();
        $ret = $tbl->updateRecord($params);

        return response()->json($ret);
    }

    public function ajax_delete(Request $request) {
        $id = $request->get('id');

        $tbl = new Banks();
        $ret = $tbl->deleteRecordById($id);

        return response()->json($ret);
    }
}
