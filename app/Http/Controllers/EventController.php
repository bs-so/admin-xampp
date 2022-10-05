<?php

namespace App\Http\Controllers;

use Auth;
use Redirect;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function event() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        return view('cms.event.list');
    }

    public function ajax_eventSearch(Request $request) {
        $params = $request->all();

        $event = new Event();
        $ret = $event->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_getEventInfo(Request $request) {
        $id = $request->get('id');

        $event = new Event();
        $ret = $event->getRecordById($id);

        return response()->json($ret);
    }

    public function ajax_addEvent(Request $request) {
        $params = $request->all();

        $mainfile = $request->file('mainImage');
        if (isset($mainfile)) {
            $now = time();
            $fileName = $mainfile->getClientOriginalName();
            $ret = $mainfile->move('uploads/events/', $now . '_' . $fileName);
            $params['img_main'] = cUrl('uploads/events/' . $now . '_' . $fileName);
        }

        $slidefile = $request->file('slideImage');
        if (isset($slidefile)) {
            $now = time();
            $fileName = $slidefile->getClientOriginalName();
            $ret = $slidefile->move('uploads/events/', $now . '_' . $fileName);
            $params['img_slide'] = cUrl('uploads/events/' . $now . '_' . $fileName);
        }

        $ret = $this->validate($request, [
            'title'  => 'required',
            'lang'    => 'required',
            'status'    => 'required',
        ]);

        $event = new Event();
        $ret = $event->insertRecord($params);

        return response()->json($ret);
    }

    public function ajax_editEvent(Request $request) {
        $params = $request->all();

        $mainfile = $request->file('mainImage');
        if (isset($mainfile)) {
            $now = time();
            $fileName = $mainfile->getClientOriginalName();
            $ret = $mainfile->move('uploads/events/', $now . '_' . $fileName);
            $params['img_main'] = cUrl('uploads/events/' . $now . '_' . $fileName);
        }

        $slidefile = $request->file('slideImage');
        if (isset($slidefile)) {
            $now = time();
            $fileName = $slidefile->getClientOriginalName();
            $ret = $slidefile->move('uploads/events/', $now . '_' . $fileName);
            $params['img_slide'] = cUrl('uploads/events/' . $now . '_' . $fileName);
        }

        $ret = $this->validate($request, [
            'title'  => 'required',
            'lang'    => 'required',
            'status'    => 'required',
        ]);

        $event = new Event();
        $ret = $event->updateRecord($params);

        return response()->json($ret);
    }

    public function ajax_deleteEvent(Request $request) {
        $id = $request->get('id');

        $event = new Event();
        $ret = $event->deleteRecord($id);

        return response()->json($ret);
    }
}
