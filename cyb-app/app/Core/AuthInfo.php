<?php

namespace App\Core;

use App\Models\Authentication;

class AuthInfo
{
    public $app_code_name;

    public $display_name;

    public $app_user_id;

    public $metadata;

    public function setAppCodeName($app_code_name)
    {
        $this->app_code_name = $app_code_name;

        return $this;
    }

    public function setDisplayName($display_name)
    {
        $this->display_name = $display_name;

        return $this;
    }

    public function setAppUserId($app_user_id)
    {
        $this->app_user_id = $app_user_id;

        return $this;
    }

    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function asAuthentication($user_id): Authentication
    {
        $auth = new Authentication();

        $auth['app_code_name'] = $this->app_code_name;
        $auth['display_name'] = $this->display_name;
        $auth['app_user_id'] = $this->app_user_id;
        $auth['user_id'] = $user_id;
        $auth['metadata'] = $this->metadata;

        return $auth;
    }
}
