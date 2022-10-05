<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    protected $table = 'olc_staff';
    protected $admin_id = 'casino_admin';
    protected $admin_pass = 'casino_admin#2021';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $record = DB::table($this->table)
            ->where('login_id', $this->admin_id)
            ->select('id')
            ->first();

        $ret = true;
        if (!isset($record)) {
            $ret = DB::table($this->table)
                ->insert([
                    'login_id'      => $this->admin_id,
                    'password'      => bcrypt($this->admin_pass),
                    'name'          => 'Administrator',
                    'email'         => 'casino_admin2021@gmail.com',
                    'role'          => USER_ROLE_ADMIN,
                    'avatar'        => '',
                    'lang'          => 'jp',
                    'status'        => STATUS_ACTIVE,
                ]);
        }

        return $ret;
    }
}
