<?php

namespace Sanprojects\Interceptor\Hooks;


class AMQPConnection extends \AMQPConnection
{
    public function __construct($credentials = [])
    {
        parent::__construct(func_get_args());
        AMQPHook::log('AMQPConnection', $credentials);
    }
}
