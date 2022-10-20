<?php

namespace App\Applications\Prejournal;

use App\Core\AuthenticationAdopter;
use App\Core\Applications;

class PrejournalAuthenticationAdopter implements AuthenticationAdopter {

    public function getAppCodeName() {
        return 'prejournal';
    }

    public function getName() {
        return 'Prejournal';
    }

    public function getIconURL() {
        return null;
    }

    public function getSupportedDataTypes() {
        return [ 'timesheet' ];
    }

    public function getAuthenticationUI() {
        $app_code_name = PrejournalAuthenticationAdopter::getAppCodeName();
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
        return new PrejournalTimesheetReader();
    }

    public function getWriter($data_type) {
        // Datatype is always timesheet in this case
        return new PrejournalTimesheetWriter();
    }

}