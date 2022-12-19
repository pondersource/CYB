<?php

namespace App\Core;

use App\Models\RecurringTask;

class Scheduler
{
    public static function scheduleTask(string $interval, string $invoke_target, array $parameters): int
    {
        $recurring_task = new RecurringTask();

        // interval is in cron form:
        // ┌───────────── minute (0 - 59)
        // │ ┌───────────── hour (0 - 23)
        // │ │ ┌───────────── day of the month (1 - 31)
        // │ │ │ ┌───────────── month (1 - 12)
        // │ │ │ │ ┌───────────── day of the week (0 - 6) (Sunday to Saturday;
        // │ │ │ │ │                                   7 is also Sunday on some systems)
        // │ │ │ │ │
        // │ │ │ │ │
        // * * * * *
        $recurring_task['interval'] = $interval;
        $recurring_task['function'] = $invoke_target;
        $recurring_task['parameters'] = json_encode($parameters);

        if (! $recurring_task->save()) {
            return 0;
        }

        return $recurring_task['id'];
    }

    public static function unscheduleTask($id): bool
    {
        return RecurringTask::where('id', $id)->delete() > 0;
    }
}
