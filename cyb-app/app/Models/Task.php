<?php

namespace App\Models;

class Task
{
    public $from_auth;
    public $to_auth;
    public $data_type;

    public function __construct($parameters)
    {
        $this->from_auth = $parameters['from_auth'];
        $this->to_auth = $parameters['to_auth'];
        $this->data_type = $parameters['data_type'];
    }
}
