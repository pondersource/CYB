<?php

namespace App\Applications\Prejournal;

use App\Core\AuthenticationAdapter;
use App\Core\ApplicationManager;
use App\Core\AuthInfo;
use App\Models\Authentication;

class PrejournalAuthenticationAdapter implements AuthenticationAdapter {

    public function getAppCodeName() {
        return 'prejournal';
    }

    public function getName() {
        return 'Prejournal';
    }

    public function getIconURL() {
        return null;
    }

    public function getAuthenticationUI() {
        $app_code_name = $this->getAppCodeName();
        return view('sample_authentication', compact('app_code_name'));
    }

    public function finalizeAuthentication(): ?AuthInfo {
        $auth_info = new AuthInfo();
        $auth_info
            ->setAppCodeName('prejournal')
            ->setDisplayName('Ismoil')
            ->setAppUserId('1')
            ->setMetadata('xxxxxxxxxxxxxxxxx');
        return $auth_info;
    }

    public function areTheSame(Authentication $auth, AuthInfo $auth_info): bool {
        return $auth['app_user_id'] == $auth_info->app_user_id;
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
        return new PrejournalTimesheetReader();
    }

    public function getWriter($auth, $data_type) {
        // Datatype is always timesheet in this case
        return new PrejournalTimesheetWriter();
    }

}