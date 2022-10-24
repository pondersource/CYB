<?php

namespace App\Core;

interface AuthenticationAdapter {

    public function getAppCodeName();
    public function getName();
    public function getIconURL();
    public function getSupportedDataTypes();
    public function getAuthenticationUI();
    public function finalizeAuthentication();
    public function registerUpdateNotifier($auth, $data_type);
    public function getReader($data_type);
    public function getWriter($data_type);

}