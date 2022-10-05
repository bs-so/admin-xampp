<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class MasterSeeder extends Seeder
{
    protected $table = 'olc_master';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ret = DB::table($this->table)
            ->truncate();

        foreach (g_enum('MasterData') as $index => $data) {
            $ret = DB::table($this->table)
                ->insert([
                    'option'    => $data[0],
                    'value'     => $data[1],
                    'type'      => $data[2],
                    'suffix'    => $data[3],
                ]);
        }

        return $ret;
    }
}
