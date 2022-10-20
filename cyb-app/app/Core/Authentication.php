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

    public function __construct($id, $app_code_name, $display_name, $user_id, $metadata, $read, $write) {
        $this->id = $id;
        $this->app_code_name = $app_code_name;
        $this->display_name = $display_name;
        $this->user_id = $user_id;
        $this->metadata = $metadata;
        $this->read = $read;
        $this->write = $write;
    }

}