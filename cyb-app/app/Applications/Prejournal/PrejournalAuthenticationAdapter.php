<?php

namespace App\Applications\Prejournal;

use App\Core\ApplicationManager;
use App\Core\AuthenticationAdapter;
use App\Core\AuthInfo;
use App\Models\Authentication;
use App\Models\AuthFunction;
use App\Models\RecurringTask;

class PrejournalAuthenticationAdapter implements AuthenticationAdapter
{
    public function getAppCodeName(): string
    {
        return 'prejournal';
    }

    public function getName(): string
    {
        return 'Prejournal';
    }

    public function getIconURL()
    {
        return null;
    }

    public function getAuthenticationUI()
    {
        $app_code_name = $this->getAppCodeName();

        return view('sample_authentication', compact('app_code_name'));
    }

    public function finalizeAuthentication(): ?AuthInfo
    {
        $auth_info = new AuthInfo();
        $auth_info
            ->setAppCodeName('prejournal')
            ->setDisplayName('Ismoil')
            ->setAppUserId('1')
            ->setMetadata('xxxxxxxxxxxxxxxxx');

        return $auth_info;
    }

    public function areTheSame(Authentication $auth, AuthInfo $auth_info): bool
    {
        return $auth['app_user_id'] == $auth_info->app_user_id;
    }

    public function registerUpdateNotifier(AuthFunction $function): bool
    {
        // TODO Do registration stuff
        // TODO Schedule check for changes

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
        $recurring_task['interval'] = '1 * * * *';
        // the function passed to recurring tasks should have an identity of function($parameters)
        // where $parameters is an array.
        $recurring_task['function'] = 'App\Applications\Prejournal\PrejournalAuthenticationAdapter::checkForChanges';
        // each item in parameters should be separated with ','
        // example: "$item1,$item2->something,$item3"
        $recurring_task['parameters'] = "$function[id]";

        return $recurring_task->save();

        // $schedule_id = RecurringManager::scheduleTask(1, 'checkForChanges', [1]);
        // $function['schedule_id'] = $schedule_id;
        // return $function->save();

        // return $this->checkForChanges($function->id);
    }

    public static function checkForChanges($parameters): bool
    {
        // extract variables.
        // cast id from string to integer.
        $function_id = (int) $parameters[0];

        // Imagining there is always an update!
        $function = AuthFunction::query()->where('id', $function_id)->first();
        ApplicationManager::onNewUpdate($function['auth_id'], $function['data_type']);

        return true;
    }

    public function unregisterUpdateNotifier(AuthFunction $function): bool
    {
        // TODO Unscheduled check for changes
        return true;
    }

    public function getSupportedDataTypes($auth): array
    {
        return ['timesheet'];
    }

    public function getReader($auth, $data_type): PrejournalTimesheetReader
    {
        // Datatype is always timesheet in this case
        return new PrejournalTimesheetReader();
    }

    public function getWriter($auth, $data_type): PrejournalTimesheetWriter
    {
        // Datatype is always timesheet in this case
        return new PrejournalTimesheetWriter();
    }
}
