<?php

namespace Sanprojects\Interceptor\Hooks;

use AMQPConnection;

class AMQPConnectionWrapper extends AMQPConnection
{
    public function __construct($credentials = [])
    {
        parent::__construct(func_get_args());
        AMQPConnectionHook::log('AMQPConnection', $credentials);
    }
}
