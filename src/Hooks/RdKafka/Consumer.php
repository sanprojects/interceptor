<?php

namespace Sanprojects\Interceptor\Hooks;

class Consumer extends \RdKafka\Consumer
{
    public function consume($timeout_ms)
    {
        return Hook::hookFunction(
            fn() => parent::consume($timeout_ms),
            func_get_args(),
            [],
            'RdKafka\Consumer::consume'
        );
    }
}
