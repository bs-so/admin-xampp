<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class SystemNotify extends Model
{
    protected $table = 'olc_notifications_wallet';

    public function getAll() {
        $records = self::select('*')->get();
        return $records;
    }

    public function setClosing($params) {
        foreach (g_enum('Languages') as $lang => $data) {
            $record = self::where('lang', $lang)
                ->select('id')
                ->first();

            $content = trans('closing.message.closed_' . $lang);
            if (isset($params['status']) && $params['status'] == 'on') {
                $content = '';
            }
            if (!isset($record) || !isset($record->id)) {
                $ret = self::insert([
                    'content'       => $content,
                    'lang'          => $lang,
                ]);
            }
            else {
                $ret = self::where('id', $record->id)
                    ->update([
                        'content'   => $content,
                    ]);
            }
        }

        return true;
    }

    public static function applyClosing($closing) {
        foreach (g_enum('Languages') as $lang => $data) {
            $record = self::where('lang', $lang)
                ->select('id')
                ->first();

            $content = trans('closing.message.closed_' . $lang);
            if (!isset($record) || !isset($record->id)) {
                $ret = self::insert([
                    'content'       => $content,
                    'lang'          => $lang,
                ]);
            }
            else {
                $ret = self::where('id', $record->id)
                    ->update([
                        'content'   => $content,
                    ]);
            }
        }
    }

    public static function removeContents() {
        $ret = self::whereRaw('1')
            ->update([
                'content'   => '',
            ]);

        return $ret;
    }

    public function updateRecord($content, $lang) {
        $record = self::where('lang', $lang)
            ->select('id')
            ->first();

        if (!isset($record) || !isset($record->id)) {
            $ret = self::insert([
                'content'       => $content,
                'lang'          => $lang,
            ]);
        }
        else {
            $ret = self::where('id', $record->id)
                ->update([
                    'content'   => $content,
                ]);
        }

        return $ret;
    }
}
