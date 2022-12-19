<?php

namespace App\Applications\Teamwork;

use App\Core\AuthenticationAdapter;
use App\Core\AuthInfo;
use App\Core\Scheduler;
use App\Models\Authentication;
use App\Models\AuthFunction;

class TeamworkAuthenticationAdapter implements AuthenticationAdapter
{
    public function getAppCodeName()
    {
        return 'teamwork';
    }

    public function getName()
    {
        return 'Teamwork';
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
            ->setAppCodeName('teamwork')
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
        $schedule_id = Scheduler::scheduleTask('1 * * * *', 'App\Applications\Teamwork\UpdateChecker', [$function['id']]);

        if ($schedule_id > 0) {
            $function['recurring_task_id'] = $schedule_id;

            return $function->save();
        }

        return false;
    }

    public function unregisterUpdateNotifier(AuthFunction $function): bool
    {
        return Scheduler::unscheduleTask($function['schedule_id']);
    }

    public function getSupportedDataTypes($auth)
    {
        return ['timesheet'];
    }

    public function getReader($auth, $data_type)
    {
        // Datatype is always timesheet in this case
        return new TeamworkTimesheetReader();
    }

    public function getWriter($auth, $data_type)
    {
        // Datatype is always timesheet in this case
        return new TeamworkTimesheetWriter();
    }
}
