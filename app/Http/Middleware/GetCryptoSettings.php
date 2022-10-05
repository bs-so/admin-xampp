<?php

namespace App\Http\Middleware;

use Session;
use Closure;
use App\Models\CryptoSettings;
use Illuminate\Http\Request;

class GetCryptoSettings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $crypto_settings = Session::get('crypto_settings');
        if (true/*!isset($crypto_settings)*/) {
            $crypto_settings = CryptoSettings::getAll();
            /*$crypto_settings[MAIN_CURRENCY] = array(
                'rate_decimals'     => MAIN_CURRENCY_DECIMALS,
            );*/
            Session::put('crypto_settings', $crypto_settings);
        }

        return $next($request);
    }
}
