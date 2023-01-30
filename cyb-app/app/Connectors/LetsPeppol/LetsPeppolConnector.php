<?php

namespace App\Connectors\LetsPeppol;

use App\Core\ApplicationManager;
use App\Core\Connector;
use App\Core\AuthInfo;
use App\Models\Authentication;
use Illuminate\Support\Facades\View;

class LetsPeppolConnector implements Connector
{

    public const CODE_NAME = 'lets_peppol';

    public function getAppCodeName()
    {
        return self::CODE_NAME;
    }

    public function getName()
    {
        return 'LetsPeppol';
    }

    public function getIconURL()
    {
        return null;
    }

    public function getAuthenticationUI()
    {
        return View::file(__DIR__.'/resources/views/authentication.blade.php');
    }

    public function finalizeAuthentication(): ?AuthInfo
    {
        $auth_info = new AuthInfo();
        $auth_info
            ->setAppCodeName(self::CODE_NAME)
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
        return ['invoice'];
    }

    public function getReader($auth, $data_type)
    {
        return null;
    }

    public function getWriter($auth, $data_type)
    {
        return null;
    }
}
