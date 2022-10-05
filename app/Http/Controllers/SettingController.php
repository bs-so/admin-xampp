<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceTokens;
use Auth;
use Session;
use App\Models\Master;
use App\Models\CryptoSettings;
use App\Models\MaintenanceContent;
use Illuminate\Http\Request;
use App\Http\Requests\SettingRequest;

class SettingController extends Controller
{
    public function index() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        $tbl = new Master();
        $datas = $tbl->getAll();

        $cryptoSettings = CryptoSettings::getAll(true);
        Session::put('crypto_settings', $cryptoSettings);

        $maintenance = new MaintenanceContent();
        $maintenanceTemp = $maintenance->getAll();

        $maintenanceContent = array();
        foreach($maintenanceTemp as $index => $temp) {
            $maintenanceContent[$temp->lang] = $temp->content;
        }

        $tokenTbl = new MaintenanceTokens();
        $maintenanceToken = $tokenTbl->getLastToken();

        return view('system.setting', [
            'datas'             => $datas,
            'cryptoSettings'    => $cryptoSettings,
            'maintenanceContent'    => $maintenanceContent,
            'maintenanceToken'      => $maintenanceToken,
            'lang'                  => 'jp',
        ]);
    }

    public function post_updateMaster(SettingRequest $request) {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        $params = $request->all();

        $tbl = new Master();
        $ret = $tbl->updateAll($params);

        return redirect()->route('setting')
            ->with('flash_message', trans('setting.message.update_success'));
    }

    public function post_updateMaintenance(Request $request) {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        $params = $request->all();
        $mode = $request->get('MAINTENANCE_MODE');

        $params['content'] = $params['content'];

        $masterparams = array();
        $masterparams['MAINTENANCE_MODE'] = $mode == 'on'? 1 : 0;

        $tbl = new Master();
        $ret = $tbl->updateAll($masterparams);

        $maintenance = new MaintenanceContent();
        $maintenance->updateRecord([
            'lang'  => $params['lang'],
            'content'   => $params['content'],
        ]);

        if ($mode == 'on') {
            // Generate new token
            $tokenTbl = new MaintenanceTokens();
            $ret = $tokenTbl->generateNewToken();
        }

        return redirect()->route('setting')
            ->with('flash_message', trans('setting.message.update_success'));
    }

    public function post_updateCrypto(Request $request) {
        $cryptoSettings = CryptoSettings::getAll(true);
        foreach ($cryptoSettings as $currency => $data) {
            $ret = $this->validate($request, [
                $currency . '-unit'             => 'required|numeric|min:0',
                $currency . '-rate_decimals'    => 'required|numeric|min:0',
                $currency . '-min_deposit'      => 'required|numeric|min:0',
                $currency . '-min_transfer'     => 'required|numeric|min:0',
                $currency . '-min_withdraw'     => 'required|numeric|min:0',
                $currency . '-transfer_fee'     => 'required|numeric|min:0',
                $currency . '-gas_price'        => 'required|numeric|min:0',
                $currency . '-gas_limit'        => 'required|numeric|min:0',
            ], [
                $currency . '-unit.*'             => sprintf(trans('setting.message.invalid_unit'), $currency),
                $currency . '-rate_decimals.*'    => sprintf(trans('setting.message.invalid_rate_decimals'), $currency),
                $currency . '-min_deposit.*'      => sprintf(trans('setting.message.invalid_min_deposit'), $currency),
                $currency . '-min_transfer.*'     => sprintf(trans('setting.message.invalid_min_transfer'), $currency),
                $currency . '-min_withdraw.*'     => sprintf(trans('setting.message.invalid_min_withdraw'), $currency),
                $currency . '-transfer_fee.*'     => sprintf(trans('setting.message.invalid_transfer_fee'), $currency),
                $currency . '-gas_price.*'        => sprintf(trans('setting.message.invalid_gas_price'), $currency),
                $currency . '-gas_limit.*'        => sprintf(trans('setting.message.invalid_gas_limit'), $currency),
            ]);
        }

        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        $params = $request->all();

        $tbl = new CryptoSettings();
        $ret = $tbl->updateAll($params);

        return redirect()->route('setting')
            ->with('flash_message', trans('setting.message.update_success'));
    }
}
