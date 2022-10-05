<?php

namespace App\Models;

use DB;
use Auth;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class MaintenanceTokens extends Model
{
    use Notifiable;
    protected $table = 'olc_maintenance_tokens';

    public function getLastToken() {
        $record = self::orderBy('id', 'desc')
            ->select('token')
            ->first();

        if (!isset($record) || !isset($record->token)) {
            return '';
        }

        return $record->token;
    }

    public function generateNewToken() {
        $user = Auth::user();
        $token = app(\Illuminate\Auth\Passwords\PasswordBroker::class)->createToken($user);

        $ret = self::truncate();

        $ret = self::insert([
            'token'     => $token,
        ]);

        return $token;
    }
}
