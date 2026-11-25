<?php

/*
 |--------------------------------------------------------------------------
 | GoBiz vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

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
        Commands\AuditPrivilegedEmailsCommand::class,
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

        // Visitor analytics: refresh the MaxMind GeoLite2 database weekly once the admin has added a license key
        $schedule->command('analytics:geoip-update')->weeklyOn(3, '04:00')
            ->when(fn () => \App\Services\Analytics\GeoIpUpdater::configured());

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
