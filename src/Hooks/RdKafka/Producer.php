<?php

namespace Sanprojects\Interceptor\Hooks\RdKafka;

use RdKafka\Producer as RdKafkaProducer;
use Sanprojects\Interceptor\Hooks\Hook;

class Producer extends RdKafkaProducer
{
    public function __construct($conf = null)
    {
        Hook::hookFunction(
            fn() => parent::__construct(...func_get_args()),
            func_get_args(),
            [],
            'RdKafka\Producer::__construct'
        );
    }

    public function produce($topic, $partition, $msgflags, $payload, $key = null, $headers = null)
    {
        return Hook::hookFunction(
            fn() => parent::produce(...func_get_args()),
            func_get_args(),
            [],
            'RdKafka\Producer::produce'
        );
    }

    public function flush($timeout_ms)
    {
        return Hook::hookFunction(
            fn() => parent::flush(...func_get_args()),
            func_get_args(),
            [],
            'RdKafka\Producer::flush'
        );
    }
}
