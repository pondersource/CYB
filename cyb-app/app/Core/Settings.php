<?php

namespace App\Core;

class Settings
{

    private string $file;

    private array $content;

    public function __construct(string $file) {
        $this->file = $file;

        if (file_exists($file)) {
            $this->content = json_decode(file_get_contents($file), true);
        }
        else {
            $this->content = [];
        }
    }

    public function __get($name) {
        return isset($this->content[$name]) ? $this->content[$name] : '';
    }

    public function __set($name, $value) {
        $this->content[$name] = $value;
    }

    public function all() {
        return $this->content;
    }

    public function save() {
        file_put_contents($this->file, json_encode($this->content));
    }

}