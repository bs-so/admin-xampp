<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class MaintenanceSeeder extends Seeder
{
    protected $table = 'olc_maintenance_contents';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ret = DB::table($this->table)
            ->truncate();

        foreach (g_enum('InitialMaintenance') as $id => $data) {
            $ret = DB::table($this->table)
                ->insert([
                    'content'       => $data[1],
                    'lang'          => $data[2],
                ]);
        }

        return $ret;
    }
}
