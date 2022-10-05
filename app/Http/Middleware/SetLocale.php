<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Make sure current locale exists.
        $user = Auth::user();
        $locale = config('app.fallback_locale');

        if ($user != null && $user->lang != null && $user->lang != '') {
            $locale = $user->lang;
        }
        else {
            if (Session::has('lang')) {
                $locale = Session::get('lang');
            }
        }
        App::setLocale($locale);

        return $next($request);
    }
}
