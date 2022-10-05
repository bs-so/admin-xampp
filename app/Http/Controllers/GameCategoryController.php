<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\GameCategory;
use Illuminate\Http\Request;

class GameCategoryController extends Controller
{
    public function ajax_search(Request $request) {
        $params = $request->all();

        $tbl = new GameCategory();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_getInfo(Request $request) {
        $id = $request->get('id');

        $tbl = new GameCategory();
        $ret = $tbl->getRecordById($id);

        return response()->json($ret);
    }

    public function ajax_add(Request $request) {
        $rules = array(
            'status'    => 'required',
        );
        foreach (g_enum('Languages') as $lang => $data) {
            $rules[$lang] = 'required';
        }
        $ret = $this->validate($request, $rules);

        $params = $request->all();

        $tbl = new GameCategory();
        $ret = $tbl->insertRecord($params);

        return response()->json($ret);
    }

    public function ajax_edit(Request $request) {
        $rules = array(
            'status'    => 'required',
        );
        foreach (g_enum('Languages') as $lang => $data) {
            $rules[$lang] = 'required';
        }
        $ret = $this->validate($request, $rules);

        $params = $request->all();

        $tbl = new GameCategory();
        $ret = $tbl->updateRecord($params);

        return response()->json($ret);
    }

    public function ajax_delete(Request $request) {
        $id = $request->get('id');

        $tbl = new GameCategory();
        $ret = $tbl->deleteRecord($id);

        return response()->json($ret);
    }
}
