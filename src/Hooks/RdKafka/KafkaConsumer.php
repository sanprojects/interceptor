<?php

namespace Sanprojects\Interceptor\Hooks;

class KafkaConsumer extends \RdKafka\KafkaConsumer
{
    public function consume($timeout_ms)
    {
        return Hook::hookFunction(
            fn() => parent::consume($timeout_ms),
            func_get_args(),
            [],
            'RdKafka\KafkaConsumer::consume'
        );
    }
}
