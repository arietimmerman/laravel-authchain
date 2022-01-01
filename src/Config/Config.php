<?php

namespace ArieTimmerman\Laravel\AuthChain\Config;

use ArieTimmerman\Laravel\AuthChain\Module\Module;

abstract class Config
{
    protected static $instance = null;
    
    protected $modules = null;

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            $class = \config('authchain.config');
            self::$instance = new $class();
        }

        return self::$instance;
    }

    public function getUIServers()
    {
        $uiServerConfigs = $this->get('authchain.modules');
    }


    abstract public function get($key);
}
