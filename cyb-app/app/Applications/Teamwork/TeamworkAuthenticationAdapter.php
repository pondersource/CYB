<?php

namespace App\Applications\Teamwork;

use App\Core\ApplicationManager;
use App\Core\AuthenticationAdapter;
use App\Core\AuthInfo;
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
        // TODO Do registration stuff
        // TODO Schedule check for changes

        // return $this->checkForChanges($function->id);
        return true;
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
        return new TeamworkTimesheetReader();
    }

    public function getWriter($auth, $data_type)
    {
        // Datatype is always timesheet in this case
        return new TeamworkTimesheetWriter();
    }
}
