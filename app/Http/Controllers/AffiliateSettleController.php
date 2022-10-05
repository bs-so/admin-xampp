<?php

namespace App\Http\Controllers;

use App\Models\AffiliateSettleData;
use Log;
use Auth;
use Session;
use App\Models\AffiliateSettle;
use App\Models\AffiliateSettleCommission;
use App\Models\AffiliateSettleBalances;
use App\Models\AffiliateSettleSummary;
use App\Models\AffiliateSettleMails;
use Illuminate\Http\Request;

class AffiliateSettleController extends Controller
{
    public function index() {
        $user = Auth::user();

        return view('affiliate.settle.list');
    }

    public function add() {
        $user = Auth::user();

        $last_settle_id = 0;
        $new_settle_id = 0;
        $current_status = AFFILIATE_SETTLE_STATUS_INIT;

        $settleTbl = new AffiliateSettle();
        $ret = $settleTbl->checkPrevSettle();
        $last_record = $settleTbl->getLastRecord();

        if ($ret == false) {
            // Exist
            $new_settle_id = $last_record->id;
            $last_settle_id = $settleTbl->getPrevSettleId($new_settle_id);
            $current_status = $last_record->status;
        }
        else {
            $last_settle_id = 0;
            if (isset($last_record)) {
                // Change to previous settle date
                $last_settle_id = $last_record->id;
            }
            $current_status = AFFILIATE_SETTLE_STATUS_LOAD_CSV;
            $new_settle_id = $settleTbl->insertRecord($user->id);
        }

        $settle_info = array(
            'last_settle_id'    => $last_settle_id,
            'new_settle_id'     => $new_settle_id,
            'current_status'    => $current_status,
        );
        Session::put('affiliate_settle_info', $settle_info);
        $crypto_settings = Session::get('crypto_settings');

        return view('affiliate.settle.add', [
            'crypto_settings'   => $crypto_settings,
            'last_settle_id'    => $last_settle_id,
            'new_settle_id'     => $new_settle_id,
            'current_status'    => $current_status,
        ]);
    }

    public function detail(Request $request) {
        $id = $request->get('id');

        $tbl = new AffiliateSettle();
        $settle = $tbl->getRecordById($id);

        $tbl = new AffiliateSettleSummary();
        $summaries = $tbl->getSummary($id);

        return view('affiliate.settle.detail', [
            'settle'    => $settle,
            'summaries' => $summaries,
        ]);
    }

    public function ajax_search(Request $request) {
        $params = $request->all();

        $tbl = new AffiliateSettle();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_delete(Request $request) {
        $id = $request->get('id');

        $tbl = new AffiliateSettle();
        $ret = $tbl->deleteRecord($id);

        return response()->json($ret);
    }

    public function ajax_updateStatus(Request $request) {
        $settle_info = Session::get('affiliate_settle_info', []);
        $status = $request->get('status');

        $tbl = new AffiliateSettle();
        $ret = $tbl->updateStatus($settle_info['new_settle_id'], $status);

        return response()->json($ret);
    }

    public function ajax_updateBasic(Request $request) {
        $params = $request->all();

        $settle_info = Session::get('affiliate_settle_info', []);
        $settle_info['begin_date'] = $params['begin_date'];
        $settle_info['end_date'] = $params['end_date'];

        $tbl = new AffiliateSettle();
        $ret = $tbl->updateBasic($settle_info['new_settle_id'], $params);

        Session::put('affiliate_settle_info', $settle_info);

        return response()->json(true);
    }

    public function ajax_uploadCsvFile(Request $request) {
        $settle_info = Session::get('affiliate_settle_info', []);
        $settle_id = $settle_info['new_settle_id'];

        $file = $request->file('file');
        if (!isset($file)) {
            return response()->json(false);
        }
        $statuses = json_decode($request->get('status'));

        $tbl = new AffiliateSettle();
        $extension = $file->getClientOriginalExtension();
        $file_name = 'settle_' . $settle_id . '.' . $extension;
        $directory = 'uploads/affiliate/settles';
        $ret = $file->move($directory, $file_name);

        $settle_info['filename'] = $directory . '/' . $file_name;
        Session::put('affiliate_settle_info', $settle_info);

        return response()->json($ret);
    }

    public function ajax_checkCsvData(Request $request) {
        $settle_info = Session::get('affiliate_settle_info', []);
        $settle_id = $settle_info['new_settle_id'];
        $file_name = $settle_info['filename'];

        $tbl = new AffiliateSettle();
        $ret = $tbl->checkCsvData($file_name);

        return response()->json($ret);
    }

    public function ajax_saveCsvSettleData(Request $request) {
        $settle_info = Session::get('affiliate_settle_info', []);
        $settle_id = $settle_info['new_settle_id'];
        $file_name = $settle_info['filename'];

        $tbl = new AffiliateSettle();
        $ret = $tbl->saveCsvData($file_name, $settle_id);

        return response()->json(true);
    }

    public function ajax_calcCommission(Request $request) {
        $settle_info = Session::get('affiliate_settle_info', []);
        $crypto_settings = Session::get('crypto_settings');
        $step = $request->get('step');
        $summary = array();

        $tbl = new AffiliateSettle();
        $ret = true;
        if ($step == 1) {
            // Load datas
            $ret = $tbl->loadSettleData($settle_info, $crypto_settings);
            if ($ret == false) {
                // Failed to prepare settle
                return response()->json(array(
                    'finished'      => 0,
                    'msg'           => trans('affiliate.settle.load_failed'),
                    'title'         => trans('ui.alert.info'),
                    'type'          => 'warning',
                    'summary'       => $summary,
                ));
            }
            else {
                return response()->json(array(
                    'finished'  => 1,
                ));
            }
        }
        else if ($step > 1) {
            if ($step == 2) {
                Log::channel('settle')->info(">> Calculate commission has started!!!");
            }
            $ret = $tbl->calcCommission($settle_info, $step, $summary);
            if ($ret == false) {
                return response()->json(array(
                    'finished'  => 1,
                ));
            }
        }

        return response()->json(array(
            'finished'  => 0,
            'msg'       => trans('affiliate.settle.calc_commission'),
            'title'     => trans('ui.alert.info'),
            'type'      => 'success',
            'summary'   => $summary,
        ));
    }

    public function ajax_saveCommission(Request $request) {
        $settle_info = Session::get('affiliate_settle_info', []);

        $tbl = new AffiliateSettleCommission();
        $ret = $tbl->saveCommission($settle_info);

        return response()->json($ret);
    }

    public function ajax_loadCommission(Request $request) {
        $settle_info = Session::get('affiliate_settle_info', []);
        $params = $request->all();
        $settle_id = $request->get('settle_id');
        $params['settle_id'] = (isset($settle_id) ? $settle_id : $settle_info['new_settle_id']);

        $tbl = new AffiliateSettleCommission();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_saveBalances(Request $request) {
        $settle_info = Session::get('affiliate_settle_info', []);

        $tbl = new AffiliateSettleBalances();
        $ret = $tbl->saveBalances($settle_info);

        return response()->json($ret);
    }

    public function ajax_loadBalances(Request $request) {
        $settle_info = Session::get('affiliate_settle_info', []);
        $params = $request->all();
        if (!isset($params['settle_id'])) {
            $params['settle_id'] = $settle_info['new_settle_id'];
        }

        $tbl = new AffiliateSettleBalances();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_finishSettle(Request $request) {
        $params = $request->all();

        $tbl = new AffiliateSettle();
        $ret = $tbl->finishSettle($params);

        return response()->json($ret);
    }

    public function ajax_loadSettleData(Request $request) {
        $params = $request->all();

        $tbl = new AffiliateSettleData();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_loadAnnounces(Request $request) {
        $params = $request->all();

        $tbl = new AffiliateSettleMails();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }
}
