<?php

namespace Sanprojects\Interceptor\Hooks\RdKafka;

use RdKafka\KafkaConsumer as RdKafkaKafkaConsumer;
use Sanprojects\Interceptor\Hooks\Hook;

class KafkaConsumer extends RdKafkaKafkaConsumer
{
    public function __construct($conf = null)
    {
        Hook::hookFunction(
            fn() => parent::__construct(...func_get_args()),
            func_get_args(),
            [],
            'RdKafka\KafkaConsumer::__construct'
        );
    }

    public function consume($timeout_ms)
    {
        return Hook::hookFunction(
            fn() => parent::consume(...func_get_args()),
            func_get_args(),
            [],
            'RdKafka\KafkaConsumer::consume'
        );
    }

    public function commit($message = null)
    {
        return Hook::hookFunction(
            fn() => parent::commit(...func_get_args()),
            func_get_args(),
            [],
            'RdKafka\KafkaConsumer::commit'
        );
    }
}
