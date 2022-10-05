<?php

namespace App\Console\Commands;

use Log;
use App\Models\Closing;
use App\Models\Master;
use App\Models\SystemNotify;
use App\Models\MaintenanceContent;
use Illuminate\Console\Command;

class CheckClosing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check-closing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $table = 'olc_closing';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $is_linux = true;
        if (strcasecmp(substr(PHP_OS, 0, 3), 'WIN') == 0){
            $is_linux = false;
        }

        $semaphore = 0;
        if ($is_linux) {
            $semaphore = sem_get(2021052501, 1);
            if (empty($semaphore)) {
                echo 'Failed to get semaphore';
                return;
            }
            if (!sem_acquire($semaphore, true)) {
                echo 'Another command is running already';
                return;
            }
        }
        $now = date('Y-m-d H:i:s');
        print_r("Now = " . $now . "\n");

        $closingTbl = new Closing();
        $setting = $closingTbl->getNowSetting();

        if ($setting->status == STATUS_BANNED) {
            // Closing entry has added
            print_r($now . ", " . $setting->start_at . "\n");
            if ($now >= $setting->start_at && $setting->apply_status == CLOSING_STATUS_NOT_APPLIED) {
                // Apply closing
                $ret = $closingTbl->updateApplyStatus($setting->id, CLOSING_STATUS_APPLIED);

                // 1. Set notify message
                $ret = SystemNotify::applyClosing($setting);
                $ret = Master::setMaintenance(STATUS_ACTIVE);
            }

            if ($now >= $setting->finish_at) {
                $ret = $closingTbl->updateStatus($setting->id,STATUS_ACTIVE);
                $ret = Master::setMaintenance(STATUS_BANNED);
                $ret = SystemNotify::removeContents();
            }
        }

        if ($is_linux) {
            sem_release($semaphore);
        }

        return 0;
    }
}
