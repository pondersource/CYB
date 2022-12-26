<?php

namespace App\Applications\Teamwork\Rest;

use App\Applications\Teamwork\Rest;

abstract class Model
{
    /**
     * @var array
     */
    private static $instances = [];

    
    protected $rest = null;
    /**
     * @var string
     */
    protected $parent = null;
    /**
     * @var string
     */
    protected $action = null;
    /**
     * @var array
     */
    protected $fields = [];
    /**
     *
     * @var string
     */
    private $hash = null;

    /**
     * Model constructor.
     *
     * @param $url
     * @param $key
     * @param $class
     * @param $hash
     *
     */
    final private function __construct($url, $key, $class, $hash)
    {
        $this->rest   = new Rest($url, $key);
        $this->hash   = $hash;
        $this->parent = strtolower(str_replace(
          ['App\Applications\Teamwork\\', '\\'],
          ['', '-'],
          $class
        ));
        if (method_exists($this, 'init')) {
            $this->init();
        }
        if (null === $this->action) {
            $this->action = str_replace('-', '_', $this->parent);
            // pluralize
            if (substr($this->action, -1) === 'y') {
                $this->action = substr($this->action, 0, -1) . 'ies';
            } else {
                $this->action .= 's';
            }
        }
        //configure request para put y post fields
        $this->rest->getRequest()
                    ->setParent($this->parent)
                    ->setFields($this->fields);
    }

    /**
     * @codeCoverageIgnore
     */
    final public function __destruct()
    {
        unset(self::$instances[$this->hash]);
    }

    /**
     * @codeCoverageIgnore
     */
    final protected function __clone()
    {
    }

    /**
     * @param $url
     * @param string $key
     */
    final public static function getInstance($url, $key)
    {
        $class = get_called_class();
        $hash = md5($class . '-' . $url . '-' . $key);
        if (!isset(self::$instances[$hash])) {
            self::$instances[$hash] = new $class($url, $key, $class, $hash);
        }

        return self::$instances[$hash];
    }
}
