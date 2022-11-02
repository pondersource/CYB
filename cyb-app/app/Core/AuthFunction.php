<?php

namespace App\Core;

class AuthFunction {

    public $id;
    public $auth_id;
    public $data_type;
    public $read;
    public $write;

    public function __construct($parameters) {
        $this->id = $parameters['id'];
        $this->auth_id = $parameters['auth_id'];
        $this->data_type = $parameters['data_type'];
        $this->read = $parameters['read'];
        $this->write = $parameters['write'];
    }

}