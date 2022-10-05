<?php

namespace App\Console\Commands;

use Auth;
use App\Models\MailModel;
use App\Models\MailQueue;
use App\Models\Trader;
use Illuminate\Http\Request;
use App\MailManager;
use Illuminate\Console\Command;

class SendMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:mail-send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
            $semaphore = sem_get(2021052001, 1);
            if (empty($semaphore)) {
                echo 'Failed to get semaphore';
                return;
            }
            if (!sem_acquire($semaphore, true)) {
                echo 'Another command is running already';
                return;
            }
        }

        $mailQueue = new MailQueue();
        $firstMail = $mailQueue->firstMail();

        if (isset($firstMail)) {
            $mailModel = new MailModel();
            $mailInfo = $mailModel->getRecordById($firstMail->announce_id);

            $traderModel = new Trader();
            $traderInfo = $traderModel->getRecordById($firstMail->user_id);

            try {
                if (empty($traderInfo)) {
                    $mailQueue->updateRecord([
                        'status'=> 2,
                    ], $firstMail->id);
                    return 0;
                }

                $ret = MailManager::sendMailWithInfo($traderInfo, $mailInfo);
                if ($ret == false) {
                    $response = 'message.mail.send_fail';
                } else {
                    $response = 1;
                }
            } catch(\Exception $e) {
                $response = 0;
            }

            $mailQueue->updateRecord([
                'status'=> $response == 1? 1 : 2,
            ], $firstMail->id);

            $mailModel->updateSuccess($firstMail->announce_id);
        }

        if ($is_linux) {
            sem_release($semaphore);
        }

        return 0;
    }
}
