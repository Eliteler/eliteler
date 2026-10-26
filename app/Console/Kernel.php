<?php

namespace App\Console;

use Illuminate\Support\Facades\DB;
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
        Commands\PlanExpiryCron::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Plan expiry reminders run once a day at the hour set in Cron Jobs (config "cron_hour")
        $cronHour = (int) DB::table('config')->where('config_key', 'cron_hour')->value('config_value');
        $schedule->command('expiry:cron')->dailyAt(sprintf('%02d:00', min(max($cronHour, 0), 23)));

        // Check health
        $schedule->command(\Spatie\Health\Commands\RunHealthChecksCommand::class)->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
