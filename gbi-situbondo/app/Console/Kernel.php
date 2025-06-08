<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check consecutive absences daily at 6 AM
        $schedule->command('notification:check-absences --threshold=3')
            ->dailyAt('06:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/absence-checks.log'));

        // Send weekly reminders (Sunday at 8 AM for next week)
        $schedule->command('notification:send-reminders --when=week_before')
            ->weeklyOn(0, '08:00') // Sunday
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/weekly-reminders.log'));

        // Send daily reminders (every day at 7 PM for tomorrow)
        $schedule->command('notification:send-reminders --when=day_before')
            ->dailyAt('19:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/daily-reminders.log'));

        // Send day-of reminders (every day at 7 AM for today)
        $schedule->command('notification:send-reminders --when=day_of')
            ->dailyAt('07:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/dayof-reminders.log'));

        // Clean up old logs weekly
        $schedule->call(function () {
            $logFiles = [
                storage_path('logs/absence-checks.log'),
                storage_path('logs/weekly-reminders.log'),
                storage_path('logs/daily-reminders.log'),
                storage_path('logs/dayof-reminders.log')
            ];

            foreach ($logFiles as $logFile) {
                if (file_exists($logFile) && filesize($logFile) > 10 * 1024 * 1024) { // 10MB
                    file_put_contents($logFile, ''); // Clear the file
                }
            }
        })->weekly();

        // Process failed jobs retry
        $schedule->command('queue:retry all')
            ->hourly()
            ->withoutOverlapping();

        // Clean up old failed jobs
        $schedule->command('queue:flush')
            ->weekly()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}