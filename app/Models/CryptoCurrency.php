<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Trader;
use Litipk\BigNumbers\Decimal;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Cache;

class CryptoCurrency extends Model
{
    protected $table = 'olc_crypto_settings';

    protected static $_all = [];

    public function getCurrencies() {
        $selector = DB::table($this->table)
            ->where('status', STATUS_VALID)
            ->select('id', 'currency', 'status');

        return $selector->get();
    }
}
