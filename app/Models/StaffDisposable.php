<?php

namespace App\Models;

use DB;
use Log;
use App\Modules\CryptoCurrencyAPI;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class StaffDisposable extends Model
{
    use Notifiable;
    protected $table = 'olc_staff_disposable';

    public function checkWallets($staff_id) {
        $wallets = self::where('status', STATUS_ACTIVE)
            ->where('staff_id', $staff_id)
            ->pluck('wallet_address', 'currency');

        // Create wallets
        foreach (g_enum('CryptoSettingsData') as $currency => $data) {
            if (isset($wallets[$currency])) {
                continue;
            }

            $newWallet = CryptoCurrencyAPI::call_generate_key($currency);
            if (!isset($newWallet['result']) || $newWallet['result'] != CryptoCurrencyAPI::_HTTP_RESPONSE_CODE_0) {
                Log::error("Create new staff disposable wallet has failed!!!");
                Log::error(json_encode($newWallet));
                continue;
            }
            if (empty($newWallet['detail']) || empty($newWallet['detail']['adr'])) {
                continue;
            }

            $ret = self::insert([
                'staff_id'          => $staff_id,
                'currency'          => $currency,
                'wallet_address'    => $newWallet['detail']['adr'],
                'wallet_privkey'    => $newWallet['detail']['pri'],
                'confirmed'         => DISPOSABLE_STATUS_NEED,
                'status'            => STATUS_ACTIVE,
            ]);
        }

        return true;
    }

    public static function getAll() {
        $records = self::where('status', STATUS_ACTIVE)
            ->select('*')
            ->get()
            ->toArray();

        return $records;
    }

    public function getRecordById($id) {
        $records = DB::table($this->table)
            ->where('id', $id)
            ->select('*')
            ->get();

        if (!isset($records) || count($records) == 0) {
            return [];
        }

        return $records[0];
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->where('status', STATUS_ACTIVE);

        if (isset($params['staff_id'])) {
            $selector->where('staff_id', $params['staff_id']);
        }
        $totalCount = $selector->count();
        $selector->select($this->table . '.*');
        $recordsFiltered = $selector->count();
        foreach ($params['order'] as $order) {
            $field = $params['columns'][$order['column']]['data'];
            $selector->orderBy($field, $order['dir']);
        }

        // offset & limit
        if (!empty($params['start']) && $params['start'] > 0) {
            $selector->skip($params['start']);
        }

        if (!empty($params['length']) && $params['length'] > 0) {
            $selector->take($params['length']);
        }

        // get records
        $records = $selector->get();
        foreach ($records as $index => $record) {
            $record->balance = 0;   //zxc
        }

        return [
            'draw' => $params['draw']+0,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }
}
