<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TraderDisposable extends Model
{
    use Notifiable;
    protected $table = 'olc_users_disposable';

}
