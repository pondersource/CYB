<?php

namespace App\Applications\Teamwork;

use App\Core\AuthenticationAdapter;
use App\Core\ApplicationManager;

class TeamworkAuthenticationAdapter implements AuthenticationAdapter {

    public function getAppCodeName() {
        return 'teamwork';
    }

    public function getName() {
        return 'Teamwork';
    }

    public function getIconURL() {
        return null;
    }

    public function getAuthenticationUI() {
        $app_code_name = TeamworkAuthenticationAdapter::getAppCodeName();
        return view('sample_authentication', compact('app_code_name'));
    }

    public function finalizeAuthentication() {
        // TODO
    }

    public function registerUpdateNotifier($auth, $data_type) {
        // TODO Do registration stuff
        
        // Imagining an update case happens immediately!
        ApplicationManager::onNewUpdate($auth, $data_type);
    }

    public function getSupportedDataTypes($auth) {
        return [ 'timesheet' ];
    }

    public function getReader($auth, $data_type) {
        // Datatype is always timesheet in this case
        return new TeamworkTimesheetReader();
    }

    public function getWriter($auth, $data_type) {
        // Datatype is always timesheet in this case
        return new TeamworkTimesheetWriter();
    }

}