<?php

namespace App\Core;

interface AuthenticationAdapter {

    public function getAppCodeName();
    public function getName();
    public function getIconURL();
    public function getAuthenticationUI();
    public function finalizeAuthentication();
    public function registerUpdateNotifier($auth, $data_type);
    public function getSupportedDataTypes($auth);
    public function getReader($auth, $data_type);
    public function getWriter($auth, $data_type);

}