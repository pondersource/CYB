<?php

namespace App\Core;

use App\Models\Authentication;
use Illuminate\Http\Request;

interface Connector
{
    public function getAppCodeName();

    public function getName();

    public function getIconURL();

    public function getAuthenticationUI();

    public function finalizeAuthentication(Request $request): ?AuthInfo;

    public function getAuthenticatedUI($auth);

    public function areTheSame(Authentication $auth, AuthInfo $auth_info): bool;

    public function registerUpdateNotifier($auth, $data_type): bool;

    public function unregisterUpdateNotifier($auth, $data_type): bool;

    public function getSupportedDataTypes($auth);

    public function getReader($auth, $data_type);

    public function getWriter($auth, $data_type);
}
