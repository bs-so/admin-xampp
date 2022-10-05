<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Check user deposit & withdraw
        $schedule->command('command:check-user-deposit')->everyMinute();
        $schedule->command('command:check-user-withdraw')->everyFifteenMinutes();
        $schedule->command('command:check-deposit-transactions')->everyFifteenMinutes();
        $schedule->command('command:check-all-user-deposits')->dailyAt('01:00');
        $schedule->command('command:check-user-withdraw-transaction')->everyFifteenMinutes();

        // Coldwallets process
        $schedule->command('command:send-transaction')->everyFiveMinutes();
        $schedule->command('command:check-transaction')->everyFiveMinutes();

        // Send mail
        $schedule->command('command:check-closing')->everyMinute();
        $schedule->command('command:mail-send')->everyMinute();
        $schedule->command('command:send-affiliate-settle-announces')->everyFifteenMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
