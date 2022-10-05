<?php

namespace App\Http\Controllers;

use App\Models\GameCategory;
use Auth;
use App\Models\GameInfo;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        $tbl = new GameCategory();
        $categories = $tbl->getAll();

        return view('games.index', [
            'categories'    => $categories,
        ]);
    }

    public function ajax_search(Request $request) {
        $params = $request->all();

        $tbl = new GameInfo();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_getInfo(Request $request) {
        $id = $request->get('id');

        $tbl = new GameInfo();
        $ret = $tbl->getRecordById($id);

        return response()->json($ret);
    }

    public function ajax_add(Request $request) {
        $ret = $this->validate($request, [
            'name'          => 'required',
            'category'      => 'required',
            'main_img'      => 'required',
            'mobile_img_jp' => 'required',
            'mobile_img_en' => 'required',
            'desc_img1_jp'  => 'required',
            'desc_img2_jp'  => 'required',
            'desc_img1_en'  => 'required',
            'desc_img2_en'  => 'required',
            'video_img'     => 'required',
            'video'         => 'required',
        ]);

        $params = $request->all();

        $tbl = new GameInfo();
        $newId = $tbl->insertRecord1($params);

        $file = $request->file('main_img');
        $fileExt = $file->getClientOriginalExtension();
        $fileName = $newId . '.' . $fileExt;
        $ret = $file->move('uploads/games/main/', $fileName);
        $params['main_img_url'] = cUrl('uploads/games/main/' . $fileName);

        $file = $request->file('mobile_img_jp');
        $fileExt = $file->getClientOriginalExtension();
        $fileName = $newId . '-1.' . $fileExt;
        $ret = $file->move('uploads/games/mobile/', $fileName);
        $params['mobile_img_jp_url'] = cUrl('uploads/games/mobile/' . $fileName);

        $file = $request->file('mobile_img_en');
        $fileExt = $file->getClientOriginalExtension();
        $fileName = $newId . '-2.' . $fileExt;
        $ret = $file->move('uploads/games/mobile/', $fileName);
        $params['mobile_img_en_url'] = cUrl('uploads/games/mobile/' . $fileName);

        $file = $request->file('desc_img1_jp');
        $fileExt = $file->getClientOriginalExtension();
        $fileName = $newId . '-1' . '.' . $fileExt;
        $ret = $file->move('uploads/games/desc/', $fileName);
        $params['desc_img1_jp_url'] = cUrl('uploads/games/desc/' . $fileName);

        $file = $request->file('desc_img2_jp');
        $fileExt = $file->getClientOriginalExtension();
        $fileName = $newId . '-2' . '.' . $fileExt;
        $ret = $file->move('uploads/games/desc/', $fileName);
        $params['desc_img2_jp_url'] = cUrl('uploads/games/desc/' . $fileName);

        $file = $request->file('desc_img1_en');
        $fileExt = $file->getClientOriginalExtension();
        $fileName = $newId . '-3' . '.' . $fileExt;
        $ret = $file->move('uploads/games/desc/', $fileName);
        $params['desc_img1_en_url'] = cUrl('uploads/games/desc/' . $fileName);

        $file = $request->file('desc_img2_en');
        $fileExt = $file->getClientOriginalExtension();
        $fileName = $newId . '-4' . '.' . $fileExt;
        $ret = $file->move('uploads/games/desc/', $fileName);
        $params['desc_img2_en_url'] = cUrl('uploads/games/desc/' . $fileName);

        $file = $request->file('video_img');
        $fileExt = $file->getClientOriginalExtension();
        $fileName = $newId . '.' . $fileExt;
        $ret = $file->move('uploads/games/video/', $fileName);
        $params['video_img_url'] = cUrl('uploads/games/video/' . $fileName);

        $file = $request->file('video');
        $fileExt = $file->getClientOriginalExtension();
        $fileName = $newId . '.' . $fileExt;
        $ret = $file->move('uploads/games/video/', $fileName);
        $params['video_url'] = cUrl('uploads/games/video/' . $fileName);

        $ret = $tbl->insertRecord2($newId, $params);

        return response()->json($ret);
    }

    public function ajax_edit(Request $request) {
        $ret = $this->validate($request, [
            'name'          => 'required',
            'category'      => 'required',
        ]);

        $params = $request->all();
        $newId = $params['id'];

        $file = $request->file('main_img');
        if (isset($file)) {
            $fileExt = $file->getClientOriginalExtension();
            $fileName = $newId . '.' . $fileExt;
            $ret = $file->move('uploads/games/main/', $fileName);
            $params['main_img_url'] = cUrl('uploads/games/main/' . $fileName);
        }

        $file = $request->file('mobile_img_jp');
        if (isset($file)) {
            $fileExt = $file->getClientOriginalExtension();
            $fileName = $newId . '-1.' . $fileExt;
            $ret = $file->move('uploads/games/mobile/', $fileName);
            $params['mobile_img_jp_url'] = cUrl('uploads/games/mobile/' . $fileName);
        }

        $file = $request->file('mobile_img_en');
        if (isset($file)) {
            $fileExt = $file->getClientOriginalExtension();
            $fileName = $newId . '-2.' . $fileExt;
            $ret = $file->move('uploads/games/mobile/', $fileName);
            $params['mobile_img_en_url'] = cUrl('uploads/games/mobile/' . $fileName);
        }

        $file = $request->file('desc_img1_jp');
        if (isset($file)) {
            $fileExt = $file->getClientOriginalExtension();
            $fileName = $newId . '-1' . '.' . $fileExt;
            $ret = $file->move('uploads/games/desc/', $fileName);
            $params['desc_img1_jp_url'] = cUrl('uploads/games/desc/' . $fileName);
        }

        $file = $request->file('desc_img2_jp');
        if (isset($file)) {
            $fileExt = $file->getClientOriginalExtension();
            $fileName = $newId . '-2' . '.' . $fileExt;
            $ret = $file->move('uploads/games/desc/', $fileName);
            $params['desc_img2_jp_url'] = cUrl('uploads/games/desc/' . $fileName);
        }

        $file = $request->file('desc_img1_en');
        if (isset($file)) {
            $fileExt = $file->getClientOriginalExtension();
            $fileName = $newId . '-3' . '.' . $fileExt;
            $ret = $file->move('uploads/games/desc/', $fileName);
            $params['desc_img1_en_url'] = cUrl('uploads/games/desc/' . $fileName);
        }

        $file = $request->file('desc_img2_en');
        if (isset($file)) {
            $fileExt = $file->getClientOriginalExtension();
            $fileName = $newId . '-4' . '.' . $fileExt;
            $ret = $file->move('uploads/games/desc/', $fileName);
            $params['desc_img2_en_url'] = cUrl('uploads/games/desc/' . $fileName);
        }

        $file = $request->file('video_img');
        if (isset($file)) {
            $fileExt = $file->getClientOriginalExtension();
            $fileName = $newId . '.' . $fileExt;
            $ret = $file->move('uploads/games/video/', $fileName);
            $params['video_img_url'] = cUrl('uploads/games/video/' . $fileName);
        }

        $file = $request->file('video');
		if (isset($video)) {
			$fileExt = $file->getClientOriginalExtension();
			$fileName = $newId . '.' . $fileExt;
			$ret = $file->move('uploads/games/video/', $fileName);
			$params['video_url'] = cUrl('uploads/games/video/' . $fileName);
		}

        $tbl = new GameInfo();
        $ret = $tbl->updateRecord($params);

        return response()->json($ret);
    }

    public function ajax_delete(Request $request)
    {
        $id = $request->get('id');

        $tbl = new GameInfo();
        $ret = $tbl->deleteRecord($id);

        return response()->json($ret);
    }
}
