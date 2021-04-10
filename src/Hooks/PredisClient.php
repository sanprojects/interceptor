<?php

namespace Sanprojects\Interceptor\Hooks;

class PredisClient extends \Predis\Client
{
    public function __call($commandID, $arguments)
    {
        return Hook::hookFunction(
            fn() => parent::__call(...func_get_args()),
            func_get_args(),
            $arguments,
            'Redis ' . $commandID
        );
    }
}
