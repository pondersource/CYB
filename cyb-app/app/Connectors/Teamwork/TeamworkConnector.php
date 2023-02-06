<?php

namespace App\Connectors\Teamwork;

use App\Core\ApplicationManager;
use App\Core\Connector;
use App\Core\AuthInfo;
use App\Models\Authentication;
use Illuminate\Http\Request;

class TeamworkConnector implements Connector
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

    public function finalizeAuthentication(Request $request): ?AuthInfo
    {
        $auth_info = new AuthInfo();
        $auth_info
            ->setAppCodeName('teamwork')
            ->setDisplayName('Ismoil')
            ->setAppUserId('1')
            ->setMetadata('xxxxxxxxxxxxxxxxx');

        return $auth_info;
    }

    public function getAuthenticatedUI($auth) {
        return view('sample_authenticated', compact('auth'));
    }

    public function areTheSame(Authentication $auth, AuthInfo $auth_info): bool
    {
        return $auth['app_user_id'] == $auth_info->app_user_id;
    }

    public function registerUpdateNotifier($auth, $data_type): bool
    {
        // TODO Do registration stuff

        // Imagining an update case happens immediately!
        ApplicationManager::onNewUpdate($auth, $data_type);

        return true;
    }

    public function unregisterUpdateNotifier($auth, $data_type): bool
    {
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
