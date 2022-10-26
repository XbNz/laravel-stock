<?php

declare(strict_types=1);

namespace App;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel;

class ConsoleKernel extends Kernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('refresh:gluetun')->everyFifteenMinutes();
        $schedule->command('recycle:temp-folder')->everyFourHours()->when(function () {
            return $this->app->make('queue')->size() === 0;
        });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
