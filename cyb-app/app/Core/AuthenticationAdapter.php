<?php

namespace App\Core;

use App\Models\Authentication;

interface AuthenticationAdapter {

    public function getAppCodeName();
    public function getName();
    public function getIconURL();
    public function getAuthenticationUI();
    public function finalizeAuthentication(): ?AuthInfo;
    public function areTheSame(Authentication $auth, AuthInfo $auth_info): bool;
    public function registerUpdateNotifier($auth, $data_type);
    public function getSupportedDataTypes($auth);
    public function getReader($auth, $data_type);
    public function getWriter($auth, $data_type);

}