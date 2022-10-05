<?php

namespace App\Console\Commands;

use DB;
use Log;
use App\MailManager;
use App\Models\AffiliateSettleCommission;
use App\Models\AffiliateSettleBalances;
use Illuminate\Console\Command;

class SendAffiliateSettleAnnounces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:send-affiliate-settle-announces';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $table = 'olc_affiliate_settle_mails';
    protected $table_users = 'olc_users';

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
            $semaphore = sem_get(2021052401, 1);
            if (empty($semaphore)) {
                echo 'Failed to get semaphore';
                return;
            }
            if (!sem_acquire($semaphore, true)) {
                echo 'Another command is running already';
                return;
            }
        }

        Log::info(">> SendAffiliateSettleAnnounces has started!!!");
        Log::info("-----------------------------------------------");

        $records = DB::table($this->table)
            ->leftJoin($this->table_users, $this->table_users . '.id', '=', $this->table . '.user_id')
            ->where('is_sent', '!=', ANNOUNCE_STATUS_SENT)
            ->select(
                $this->table . '.*',
                $this->table_users . '.nickname',
                $this->table_users . '.email'
            )
            ->get();

        $commissionTbl = new AffiliateSettleCommission();
        $balanceTbl = new AffiliateSettleBalances();
        Log::info("    Total Count = " . count($records));
        $success = 0; $failed = 0;
        foreach ($records as $index => $record) {
            $userid = $record->userid;
            $nickname = $record->nickname;
            $email = $record->email;

            $commissions = $commissionTbl->getUserCommissions($record->settle_id, $record->user_id);
            if (count($commissions) == 0) {
                continue;
            }
            $balances = $balanceTbl->getUserBalances($record->settle_id, $record->user_id);
            $ret = MailManager::send_affiliate_settle_announce($record, $commissions, $balances);
            if ($ret == true) {
                Log::info("      " . $userid . ", " . $nickname . ", " . $email . " : " . ($ret == true) ? "Sent" : "Failed");
                $ret = DB::table($this->table)
                    ->where('id', $record->id)
                    ->update([
                        'is_sent'   => ANNOUNCE_STATUS_SENT,
                    ]);
                $success ++;
                sleep(5);
            }
            else {
                $ret = DB::table($this->table)
                    ->where('id', $record->id)
                    ->update([
                        'is_sent'   => ANNOUNCE_STATUS_FAILED,
                    ]);
                $failed ++;
            }
        }

        Log::info("-----------------------------------------------");
        Log::info(">> Success = " . $success . ", Failed = " . $failed);
        Log::info("");

        if ($is_linux) {
            sem_release($semaphore);
        }

        return 0;
    }
}
