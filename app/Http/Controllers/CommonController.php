<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function profile() {
        $user = Auth::user();

        return view('profile', [
            'user'  => $user,
        ]);
    }

    public function updateProfile(Request $request) {
        $user = Auth::user();
        $id = $user->id;

        // Validate name, email and password fields
        $this->validate($request, [
            'name'          => 'required|max:64',
            'password'      => 'confirmed',
            'email'         => 'required|max:64',
        ]);

        // Retrieving only the name, login-id and password data
        $input = $request->only('name', 'password', 'gender', 'birthday', 'mobile', 'postal_code', 'address', 'avatar');
        if (empty($input['password'])) {
            unset($input['password']);
        } else {
            $input['password'] = bcrypt($input['password']);
        }

        $file = $request->file('avatar');
        if (isset($file)) {
            $extension = $file->getClientOriginalExtension();
            $userId = $user->login_id;
            $fileName = $userId . '.' . $extension;
            $ret = $file->move('uploads/avatars', $fileName);
            $input['avatar'] = $fileName;
        }

        $tbl = new User();
        $tbl->updateRecordById($id, $input);

        return redirect()->route('profile')
            ->with('flash_message', 'profile.message.update_success');
    }

    public function mark_notify(Request $request)
    {
        $id = $request->get('id');

        $notificationTbl = new Notification();
        $type = $notificationTbl->markRecord($id);

        switch ($type) {
            case NOTIFY_TYPE_DEPOSIT:
                return redirect('/history?type=' . $type); break;
            case NOTIFY_TYPE_EXCHANGE:
                return redirect('/history?type=' . $type); break;
            case NOTIFY_TYPE_WITHDRAW:
                return redirect('/history?type=' . $type); break;
            case NOTIFY_TYPE_JACKPOT:
                return redirect('/history?type=' . $type); break;
        }

        return redirect()->back();
    }

    public function ajax_getBalanceDecimals(Request $request) {
        $tbl = new CryptoCurrency();
        $ret = $tbl->getBalanceDecimals();

        return response()->json($ret);
    }

    public function ajax_addNotification(Request $request) {
        $user = Auth::user();
        $params = $request->all();

        $data = array();

        $tbl = new Notification();
        $ret = $tbl->insertRecord($user->id, array(
            'type'          => $params['type'],
            'data'          => $data,
            'read_status'   => NOTIFY_STATUS_NOT_READ,
        ));

        return response()->json($ret);
    }
}
