<?php

namespace App\Core;

class Task {

    public $from_auth;
    public $to_auth;
    public $data_type;

    public function __construct($from_auth, $to_auth, $data_type) {
        $this->from_auth = $from_auth;
        $this->to_auth = $to_auth;
        $this->data_type = $data_type;
    }

}