<?php

namespace App\Connectors\LetsPeppol\ACube;

class Constants
{

    public const SANDBOX_BASE_URL = 'https://peppol-sandbox.api.acubeapi.com';
    public const SANDBOX_LOGIN_URL = 'https://common-sandbox.api.acubeapi.com/login';

    public const PRODUCTION_BASE_URL = 'https://peppol.api.acubeapi.com';
    public const PRODUCTION_LOGIN_URL = 'https://common.api.acubeapi.com/login';

    public const USE_SANDBOX = true;

    public const BASE_URL = self::USE_SANDBOX ? self::SANDBOX_BASE_URL : self::PRODUCTION_BASE_URL;
    public const LOGIN_URL = self::USE_SANDBOX ? self::SANDBOX_LOGIN_URL : self::PRODUCTION_LOGIN_URL;

    public const USERNAME = self::USE_SANDBOX ? Secrets::SANDBOX_USERNAME : Secrets::PRODUCTION_USERNAME;
    public const PASSWORD = self::USE_SANDBOX ? Secrets::SANDBOX_PASSWORD : Secrets::PRODUCTION_PASSWORD;

}