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
    public function getAppCodeName()
    {
        return 'prejournal';
    }

    public function getName()
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
        $recurring_task['interval'] = 1;
        $recurring_task['function'] = 'App\Applications\Prejournal\PrejournalAuthenticationAdapter::checkForChanges';
        $recurring_task['parameters'] = "[$function->id]";
        return $recurring_task->save();

        // $schedule_id = RecurringManager::scheduleTask(1, 'checkForChanges', [1]);
        // $function['schedule_id'] = $schedule_id;
        // return $function->save();
        
        // return $this->checkForChanges($function->id);
    }

    public static function checkForChanges($function_id): bool
    {
        // Imagining there is always an update!
        $function = AuthFunction::query()->where('id', $function_id)->first();
        ApplicationManager::onNewUpdate($function['auth_id'], $function['data_type']);
        return true;
    }

    public function unregisterUpdateNotifier(AuthFunction $function): bool
    {
        // TODO Unschedule check for changes
        return true;
    }

    public function getSupportedDataTypes($auth)
    {
        return ['timesheet'];
    }

    public function getReader($auth, $data_type)
    {
        // Datatype is always timesheet in this case
        return new PrejournalTimesheetReader();
    }

    public function getWriter($auth, $data_type)
    {
        // Datatype is always timesheet in this case
        return new PrejournalTimesheetWriter();
    }
}
