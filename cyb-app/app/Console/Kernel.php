<?php

namespace App\Console;

use App\Models\RecurringTask;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        // Horizon includes a metrics dashboard which provides information
        // regarding your job and queue wait times and throughput.
        // metrics dashboard needs below command executed periodically.
        $schedule->command('horizon:snapshot')->everyFiveMinutes();

        // recurring tasks schedule.
        $tasks = RecurringTask::all();

        foreach ($tasks as $task) {
            $function = $task->function;
            $interval = $task->interval;
            $parameters = json_decode($task->parameters, true);

            $schedule->call(
                new $function($parameters)
            )->everyMinute();
        }
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
