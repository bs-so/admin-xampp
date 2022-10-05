<?php

namespace App\Models;

use DB;
use Log;
use Auth;
use Session;
use Litipk\BigNumbers\Decimal;
use Illuminate\Database\Eloquent\Model;

class CasinoApi extends Model
{
    public static function register($user) {
        // Call casino API
        $params = array(
            'userid'        => $user['userid'],
            'password'      => isset($user['password_plain']) ? $user['password_plain'] : '',
            'email'         => $user['email'],
            'firstname'     => $user['firstname'],
            'lastname'      => $user['lastname'],
            'nickname'      => $user['nickname'],
            'country'       => strtolower($user['country']),
            'city'          => $user['city'],
            'birth'         => str_replace('-', '', $user['birthday']),
            'affiid'        => $user['referrer'],
        );
        $params = json_encode($params);
        Log::info("Call casino register API : " . $params);
        $ret = g_sendHttpRequest(CASINO_USERINFO_URL, HTTP_METHOD_POST, $params);
        Log::info($ret);

        $ret = json_decode($ret, true);
        if (isset($ret['code']) && $ret['code'] != 200) {
            if ($ret['code'] == 705) {
                // Duplicate user
                return CASINO_REGISTER_EXIST;
            }
            return CASINO_REGISTER_FAILED;
        }
        if (isset($ret['result']) && $ret['result'] != 'ok') {
            return CASINO_REGISTER_FAILED;
        }

        return CASINO_REGISTER_SUCCESS;
    }

    public static function login($params, $session_id, &$code, &$message) {
        $params = json_encode($params);
        Log::info("Call casino login API : " . $params);
        Log::info("SessionID : " . $session_id);
        $headers = array(
            'Session: ' . $session_id,
        );
        $ret = g_sendHttpRequest(CASINO_SESSION_URL, HTTP_METHOD_POST, $params, $headers);
        Log::info($ret);

        $ret = json_decode($ret, true);
        $code = 0;
        if (isset($ret['code'])) {
            // Failed
            $code = $ret['code'];
            $message = (isset($ret['message']) ? $ret['message'] : '');
            if ($ret['code'] == 702) {
                // Invalid register UserId
                return false;
            }
            else if ($ret['code'] == 703) {
                // Wrong password
                return false;
            }
        }
        if (isset($ret['nickname'])) {
            // Success
            $code = 200;
            return true;
        }

        return false;
    }

    public static function checkUserInfo($userid) {
        $params = json_encode(array(
            'userid'    => $userid,
        ));
        Log::info(">> Call casino check userinfo API : " . $params);
        $code = 0;
        $ret = g_sendHttpRequest(CASINO_USERINFO_CHECK_URL, HTTP_METHOD_POST, $params, "", $code);
        Log::info("    Return: " . $code);

        return $code;
    }

    public static function logout() {
        Log::info(">> Call casino logout API.");
        $user = Auth::user();
        if (!isset($user)) {
            return true;
        }

        $session_id = $user->session_id;
        $headers = array(
            'Session: ' . $session_id,
        );
        $ret = g_sendHttpRequest(CASINO_SESSION_URL, HTTP_METHOD_DELETE, '', $headers);
        Log::info("    Result: " . $ret);

        $ret = json_decode($ret, true);
        if (isset($ret['result']) && $ret['result'] == 'ok') {
            return true;
        }

        return false;
    }
}
