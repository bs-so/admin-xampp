<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use App\Models\StaffDeposit;
use App\Models\StaffWithdraw;
use Illuminate\Http\Request;
use App\Http\Requests\StaffRequest;

class StaffController extends Controller
{
    public function index() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        return view('staff.list', [
        ]);
    }

    public function add()
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        return view('staff.add', [
        ]);
    }

    public function edit(Request $request)
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        $id = $request->get('id');

        $tbl = new User();
        $staff = $tbl->getRecordById($id);

        return view('staff.edit', [
            'id'    => $id,
            'staff' => $staff,
        ]);
    }

    public function download() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        $file = '管理者一覧.csv';

        $tbl = new User();
        $ret = $tbl->makeCsv($file);

        return response()->download($file);
    }

    public function deposit_list()
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        return view('history.history-staff-deposit');
    }

    public function deposit_download() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        $file = '管理者入金履歴.csv';

        $tbl = new StaffDeposit();
        $ret = $tbl->makeCsv($file);

        return response()->download($file);
    }

    public function withdraw_list()
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        return view('history.history-staff-withdraw', [
        ]);
    }

    public function profile()
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        $user = Auth::user();

        $tbl = new User();
        $staff = $tbl->getRecordById($user->id);

        return view('staff.edit', [
            'id'    => $user->id,
            'staff' => $staff,
        ]);
    }

    public function post_add(StaffRequest $request)
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        $record = $request->only('login_id', 'name', 'password', 'email', 'role');
        $record['password'] = bcrypt($record['password']);
        $record['avatar'] = '';
        $record['lang'] = 'jp';
        $record['status'] = STATUS_ACTIVE;

        if ($user->role > $record['role']) {
            return redirect()->back();
        }

        $tbl = new User();
        $tbl->createRecord($record);

        return redirect()->route('staff')
            ->with('flash_message', 'staff.message.add_success');
    }

    public function post_edit(StaffRequest $request) {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        $id = $request->get('id');

        // Retrieving only the name, login-id and password data
        $input = $request->only('login_id', 'name', 'password', 'gender', 'birthday', 'mobile_no', 'email', 'role', 'status', 'avatar');
        if (empty($input['password'])) {
            unset($input['password']);
        } else {
            $input['password'] = bcrypt($input['password']);
        }

        if ($user->role > $input['role']) {
            return redirect()->back();
        }

        $file = $request->file('avatar');
        if (isset($file)) {
            $extension = $file->getClientOriginalExtension();
            $userId = $request->get('login_id');
            $fileName = $userId . '.' . $extension;
            $ret = $file->move('uploads/avatars', $fileName);
            $input['avatar'] = $fileName;
        }
        else {
            $input['avatar'] = '';
        }

        $userTbl = new User();
        $userTbl->updateRecordById($id, $input);

        return redirect()->route('staff')
            ->with('flash_message', 'staff.message.edit_success');
    }

    public function ajax_search(Request $request)
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        $params = $request->all();
        $tbl = new User();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_delete(Request $request)
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN)
        {
            return redirect()->back();
        }

        $id = $request->get('id');

        $tbl = new User();
        $ret = $tbl->deleteRecordById($id);

        return response()->json($ret);
    }

    public function ajax_deposit_search(Request $request)
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        $params = $request->all();
        $params['staff_id'] = $user->id;

        $tbl = new StaffDeposit();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);

    }

    public function ajax_withdraw_search(Request $request)
    {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        $params = $request->all();
        $params['staff_id'] = $user->id;

        $tbl = new StaffWithdraw();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }
}
