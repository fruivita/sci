<?php

namespace App\Console;

use App\Enums\QueueType;
use App\Jobs\ImportCorporateStructure;
use App\Jobs\ImportPrintLog;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule
            ->job(new ImportCorporateStructure(), QueueType::Corporate->value)
            ->dailyAt('1:00');

        $schedule
            ->job(new ImportPrintLog(), QueueType::PrintLog->value)
            ->dailyAt('2:00');
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
