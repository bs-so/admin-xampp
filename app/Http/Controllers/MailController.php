<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\MailModel;
use App\Models\MailQueue;
use App\Models\Trader;
use Illuminate\Http\Request;

class MailController extends Controller
{
    public function mail() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        return view('cms.mail.list');
    }

    public function detail(Request $request) {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO)
        {
            return redirect()->back();
        }

        $id = $request->get('id');
        $mailModel = new MailModel();
        $mail = $mailModel->getRecordById($id);

        $mailQueue = new MailQueue();
        $users = $mailQueue->usersByMailID($id);

        return view('cms.mail.mail', [
            'title'     => $mail->title,
            'receiver'  => '〇〇〇〇',
            'content'   => $mail->content,
            'created_at' => $mail->created_at,
            'users'     => $users,
        ]);
    }

    public function ajax_mailSearch(Request $request) {
        $params = $request->all();

        $mailModel = new MailModel();
        $ret = $mailModel->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_addMails(Request $request) {
        $params = $request->all();

        $ret = $this->validate($request, [
            'title'  => 'required',
            'content'    => 'required',
        ]);

        if ($params['type'] == 2 || $params['type'] == 3) {
            $ret = $this->validate($request, [
                'filterDates'  => 'required',
            ]);
        }

        if ($params['type'] == 4) {
            $ret = $this->validate($request, [
                'csvFile'  => 'required',
            ]);
        }

        if ($params['type'] == 5) {
            $ret = $this->validate($request, [
                'userSpec'  => 'required',
            ]);
        }

        $traderModel = new Trader();
        $mailModel = new MailModel();
        $mailQueue = new MailQueue();

        $mainfile = $request->file('csvFile');
        $traders = array();
        if ($params['type'] == 4) {
            $row = 1;
            if (($handle = fopen($mainfile, "r")) !== FALSE) {
                while (($data = fgetcsv($handle)) !== FALSE) {
                    $userid = preg_split ('/\t|,/', $data[0])[0];
                    $trader = $traderModel->getRecordByUserID($userid);

                    if (isset($trader)) {
                        $traders[] = $trader->id;
                    }
                }
                fclose($handle);
            }
        } else if ($params['type'] == 2 || $params['type'] == 3) {
            $traders = $traderModel->getUsers($params);
        } else if ($params['type'] == 5) {
            $userids = preg_split('/\s|,/', $params['userSpec']);
            foreach($userids as $userid) {
                $trader = $traderModel->getRecordByUserID($userid);
                if (isset($trader)) {
                    $traders[] = $trader->id;
                }
            }
        }

        $params['total'] = count($traders);
        $new_id = $mailModel->insertRecord($params);

        foreach($traders as $trader):
            $mailQueue->insertRecord([
                'announce_id'   => $new_id,
                'user_id'       => $trader,
                'status'        => 0,
            ]);
        endforeach;

        return response()->json($ret);
    }
}
