<?php

namespace App\Connectors\LetsPeppol;

use App\Connectors\LetsPeppol\Models\Identity;
use App\Core\ApplicationManager;
use App\Core\Connector;
use App\Core\AuthInfo;
use App\Models\Authentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class LetsPeppolConnector implements Connector
{

    public const CODE_NAME = 'lets_peppol';

    private ?LetsPeppolService $service = null;

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

    public function finalizeAuthentication(Request $request): ?AuthInfo
    {
        $identity = $this->getService()->createIdentity($request->user()['id'], $request->toArray());

        if ($identity == null) {
            return null;
        }

        $auth_info = new AuthInfo();
        $auth_info
            ->setAppCodeName(self::CODE_NAME)
            ->setDisplayName($payload['name'])
            ->setAppUserId($identity['id'])
            ->setMetadata($identity['id']);

        return $auth_info;
    }

    public function getAuthenticatedUI($auth) {
        $identity = $this->getService()->getIdentity($auth['user_id']);
        return View::file(__DIR__.'/resources/views/authenticated.blade.php', compact($identity));
    }

    public function areTheSame(Authentication $auth, AuthInfo $auth_info): bool
    {
        return $auth['app_user_id'] == $auth_info->app_user_id;
    }

    public function registerUpdateNotifier($auth, $data_type): bool
    {
        $identity = $this->getService()->getIdentity($auth['user_id']);

        if ($identity == null) {
            return false;
        }

        $identity['auth_id'] = $auth['id'];

        return $this->getService()->updateIdentity($identity);
    }

    public function unregisterUpdateNotifier($auth, $data_type): bool
    {
        $identity = $this->getService()->getIdentity($auth['user_id']);

        if ($identity == null) {
            return false;
        }

        $identity['auth_id'] = null;
        
        return $this->getService()->updateIdentity($identity);
    }

    public function getSupportedDataTypes($auth)
    {
        return ['invoice'];
    }

    public function getReader($auth, $data_type)
    {
        $service = $this->getService();
        $identity = $service->getIdentity($auth['user_id']);

        return new LetsPeppolReader($service, $identity);
    }

    public function getWriter($auth, $data_type)
    {
        $service = $this->getService();
        $identity = $service->getIdentity($auth['user_id']);

        return new LetsPeppolWriter($service, $identity);
    }

    private function getService()
    {
        if ($this->service == null) {
            $this->service = new LetsPeppolService();
        }
        
        return $this->service;
    }
}
