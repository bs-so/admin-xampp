<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function setLocale(Request $request)
    {
        $locale = $request->get('locale');
        $user = Auth::user();

        if ($user == null) {
            Session::put('lang', $locale);
        }
        else {
            $tbl = new User();
            $tbl->updateLang($user->id, $locale);
        }

        return back();
    }
}
