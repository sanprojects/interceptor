<?php

namespace Sanprojects\Interceptor\Hooks;


class AMQPExchange extends \AMQPExchange
{
    public function publish($message, $routing_key = null, $flags = AMQP_NOPARAM, array $attributes = array()) 
    {
        return Hook::hookFunction(
            fn() => parent::publish(...func_get_args()),
            func_get_args(),
            [],
            'AMQPConnection::publish'
        );
    }
}

