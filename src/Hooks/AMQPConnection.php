<?php

namespace Sanprojects\Interceptor\Hooks;


class AMQPConnection extends \AMQPConnection
{
    public function __construct($credentials = [])
    {
        parent::__construct(func_get_args());

        Hook::hookFunction(
            fn() => parent::__construct(...func_get_args()),
            func_get_args(),
            [],
            'AMQPConnection::__construct'
        );
    }
}
