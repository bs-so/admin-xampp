<?php

namespace App\Models;

use DB;
use Litipk\BigNumbers\Decimal;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CryptoSettings extends Model
{
    use Notifiable;
    protected $table = 'olc_crypto_settings';

    public static function getAll($all = false) {
        $selector = self::whereRaw('1');
        if (!$all) {
            $selector->where('status', STATUS_ACTIVE);
        }

        $records = $selector->select('*')->get();

        $result = array();
        foreach ($records as $index => $record) {
            $result[$record->currency] = array(
                'currency'      => $record->currency,
                'name'          => $record->name,
                'type'          => $record->type,
                'unit'          => $record->unit,
                'rate_decimals' => $record->rate_decimals,
                'min_deposit'   => $record->min_deposit,
                'min_transfer'  => $record->min_transfer,
                'min_withdraw'  => $record->min_withdraw,
                'transfer_fee'  => $record->transfer_fee,
                'gas_price'     => $record->gas_price,
                'gas_limit'     => $record->gas_limit,
                'gas'           => $record->gas,
                'status'        => $record->status,
            );
        }

        return $result;
    }

    public function updateAll($params) {
        $ret = true;

        $records = self::select('currency')->get();
        foreach ($records as $index => $record) {
            $currency = $record->currency;
            $data = array();
            foreach ($params as $option => $value) {
                $array = explode('-', $option);
                if ($currency == $array[0]) {
                    $data[$array[1]] = $value;
                }
            }

            if (isset($data['gas_price']) && isset($data['gas_limit'])) {
                $gas = Decimal::create($data['gas_price']);
                $gas = $gas->div(Decimal::create(GAS_UNIT));
                $gas = $gas->div(Decimal::create(GAS_UNIT));
                $gas = $gas->mul(Decimal::create($data['gas_limit']));
                $data['gas'] = $gas->__toString();
            }

            $ret = self::where('currency', $currency)
                ->update($data);
        }

        return $ret;
    }
}
