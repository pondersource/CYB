<?php

namespace App\Core;

class Settings implements \ArrayAccess
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

    public function offsetExists(mixed $offset): bool {
        return isset($this->content[$offset]);
    }

    public function offsetGet(mixed $offset): mixed {
        return isset($this->content[$offset]) ? $this->content[$offset] : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        if (is_null($offset)) {
            throw new \Exception('Value can only be assigned with a key');
        } else {
            $this->content[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void {
        unset($this->content[$offset]);
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