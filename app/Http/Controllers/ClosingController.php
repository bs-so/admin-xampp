<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Closing;
use App\Models\Master;
use App\Models\SystemNotify;
use Illuminate\Http\Request;

class ClosingController extends Controller
{
    public function index() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        $tbl = new Closing();
        $setting = $tbl->getNowSetting();

        return view('system.closing', [
            'setting'   => $setting,
        ]);
    }

    public function post_update(Request $request) {
        $this->validate($request, [
            'start_at_date'     => 'required',
            'start_at_time'     => 'required',
            'finish_at_date'    => 'required',
            'finish_at_time'    => 'required',
        ]);

        $params = $request->all();

        $tbl = new Closing();
        $ret = $tbl->updateSetting($params);

        if (isset($params['status']) && $params['status'] == 'on') {
            $ret = Master::setMaintenance(STATUS_BANNED);
        }

        if ($ret == false) {
            return redirect()->route('closing')
                ->with('error_message', trans('closing.message.op_failed'));
        }

        return redirect()->route('closing')
            ->with('flash_message', trans('closing.message.op_success'));
    }
}
