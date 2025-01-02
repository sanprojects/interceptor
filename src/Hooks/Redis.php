<?php

namespace Sanprojects\Interceptor\Hooks;

class Redis extends \Redis
{
    public function connect(
        $host,
        $port = 6379,
        $timeout = 0.0,
        $reserved = null,
        $retryInterval = 0,
        $readTimeout = 0.0
    ) {
        return Hook::hookFunction(fn() => parent::connect(...func_get_args()), func_get_args());
    }

    public function get($key)
    {
        $name = __FUNCTION__;

        return Hook::hookFunction(fn() => parent::{$name}(...func_get_args()), func_get_args());
    }

    public function set($key, $value, $timeout = null)
    {
        $name = __FUNCTION__;

        return Hook::hookFunction(fn() => parent::{$name}(...func_get_args()), func_get_args());
    }
}
