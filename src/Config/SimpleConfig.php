<?php

namespace ArieTimmerman\Laravel\AuthChain\Config;

class SimpleConfig extends Config
{
    public function __construct()
    {
        // optionally implement this
    }

    public function get($key)
    {
        return \config($key);
    }
}
