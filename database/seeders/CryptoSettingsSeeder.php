<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class CryptoSettingsSeeder extends Seeder
{
    protected $table = 'olc_crypto_settings';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ret = DB::table($this->table)
            ->truncate();

        foreach (g_enum('CryptoSettingsData') as $currency => $data) {
            $ret = DB::table($this->table)
                ->insert([
                    'currency'      => $currency,
                    'name'          => $data[0],
                    'type'          => $data[1],
                    'symbol'        => $currency . '/' . MAIN_CURRENCY,
                    'unit'          => $data[2],
                    'rate_decimals' => $data[3],
                    'min_deposit'   => $data[4],
                    'min_withdraw'  => $data[5],
                    'transfer_fee'  => $data[6],
                    'gas_price'     => $data[7],
                    'gas_limit'     => $data[8],
                    'gas'           => $data[9],
                    'status'        => STATUS_ACTIVE,
                ]);
        }

        return $ret;
    }
}
