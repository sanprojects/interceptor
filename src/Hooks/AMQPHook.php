<?php

namespace Sanprojects\Interceptor\Hooks;

class AMQPHook extends Hook
{
    protected const HOOKED_CLASSES = [
        \AMQPConnection::class => AMQPConnection::class,
        \AMQPExchange::class => AMQPExchange::class,
    ];
}
