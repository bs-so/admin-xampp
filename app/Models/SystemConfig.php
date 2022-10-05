<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class SystemConfig extends Authenticatable
{
    use Notifiable;
    protected $table = 'olc_master';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'option', 'value'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

    protected $_allSettings = null;

    public function getAll($forceRefresh = false)
    {
        if ($forceRefresh || empty($this->_allSettings)) {
            $this->_allSettings = DB::table($this->table)
                //->pluck('option_value', 'option_name');
                ->pluck('value', 'option');
        }


        foreach($this->_allSettings as $index => $setting) {
        	if($index == 'MAINTENANCE_PLAN')
				$this->_allSettings['MAINTENANCE_PLAN'] = json_decode($setting, true);
        }

        return $this->_allSettings;
    }

    public function updateValue($option, $value) {
        $ret = DB::table($this->table)
            ->where('option', $option)
            ->update([
                'value' => $value,
            ]);

        return $ret;
    }

    public function updateSetting($data) {
        if (!isset($data) || count($data) == 0) {
            return 0;
        }

	    $business_plan = array();
        foreach(g_enum('WeekDay') as $index => $weekID) {
	        $business_plan[$index]['begin_time'] = $data['BUSINESS_WEEK_BEGIN_TIME' . $index];
			if(!isset($data['STATUS' . $index]) || $data['STATUS' . $index] != 'on')
				$data['STATUS' . $index] = 0;
			else
				$data['STATUS' . $index] = 1;

	        $business_plan[$index]['is_maintenance'] = $data['STATUS' . $index];
	        $business_plan[$index]['end_time'] = $data['BUSINESS_WEEK_END_TIME' . $index];
        }

	    $business_plan = json_encode($business_plan);

	    $data['MAINTENANCE_PLAN'] = $business_plan;

        foreach ($data as $name => $value) {
            if ($name == 'CONTROL_RATE_FOR_RISK') {
                $value = ($value == 'on') ? 1 : 0;
            }
            if ($value == 'on') $value = 1;
            DB::table($this->table)
                ->where('option_name', $name)
                ->update(['option_value' => $value]);
        }

        return 1;
    }
}
