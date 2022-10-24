<?php

namespace App\Core;

class Authentication {

    public $id;
    public $app_code_name;
    public $display_name;
    public $user_id;
    public $metadata;

    // TODO Read and write should be separate in readers and writers table
    public $read;
    public $write;

    public function __construct($parameters) {
        $this->id = $parameters['id'];
        $this->app_code_name = $parameters['app_code_name'];
        $this->display_name = $parameters['display_name'];
        $this->user_id = $parameters['user_id'];
        $this->metadata = $parameters['metadata'];
        $this->read = $parameters['read'];
        $this->write = $parameters['write'];
    }

}