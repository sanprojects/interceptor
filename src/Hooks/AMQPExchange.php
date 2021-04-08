<?php

namespace Sanprojects\Interceptor\Hooks;


class AMQPExchange extends \AMQPExchange
{
    public function publish($message, $routing_key = null, $flags = AMQP_NOPARAM, array $attributes = array())
    {
        return AMQPHook::hookFunction([$this, 'parent::' . __FUNCTION__], func_get_args());
    }
}
