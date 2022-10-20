<?php

namespace App\Applications\Teamwork;

use App\Core\AuthenticationAdopter;
use App\Core\Applications;

class TeamworkAuthenticationAdopter implements AuthenticationAdopter {

    public function getAppCodeName() {
        return 'teamwork';
    }

    public function getName() {
        return 'Teamwork';
    }

    public function getIconURL() {
        return null;
    }

    public function getSupportedDataTypes() {
        return [ 'timesheet' ];
    }

    public function getAuthenticationUI() {
        $app_code_name = TeamworkAuthenticationAdopter::getAppCodeName();
        return view('sample_authentication', compact('app_code_name'));
    }

    public function finalizeAuthentication() {
        // TODO
    }

    public function registerUpdateNotifier($auth, $data_type) {
        // TODO Do registration stuff
        
        // Imagining an update case happens immediately!
        Applications::onNewUpdate($auth, $data_type);
    }

    public function getReader($data_type) {
        // Datatype is always timesheet in this case
        return new TeamworkTimesheetReader();
    }

    public function getWriter($data_type) {
        // Datatype is always timesheet in this case
        return new TeamworkTimesheetWriter();
    }

}