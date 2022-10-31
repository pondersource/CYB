<?php

namespace App\Core;

class Authentication {

    public $id;
    public $app_code_name;
    public $display_name;
    public $app_user_id;
    public $user_id;
    public $metadata;

    public function __construct($parameters) {
        $this->id = $parameters['id'];
        $this->app_code_name = $parameters['app_code_name'];
        $this->display_name = $parameters['display_name'];
        $this->app_user_id = $parameters['app_user_id'];
        $this->user_id = $parameters['user_id'];
        $this->metadata = $parameters['metadata'];
    }

}