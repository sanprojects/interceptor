<?php

namespace Sanprojects\Interceptor\Hooks;

use Redis;

class RedisWrapper extends Redis
{
    public function __call($name, $arguments) {
        return PDOHook::hookFunction([$this, $name], func_get_args());
    }
}
