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

    public function flush($timeout_ms)
    {
        return Hook::hookFunction(
            fn() => parent::flush(...func_get_args()),
            func_get_args(),
        );
    }

    public function initTransactions(int $timeoutMs)
    {
        return Hook::hookFunction(
            fn() => parent::initTransactions(...func_get_args()),
            func_get_args(),
        );
    }

    public function beginTransaction()
    {
        return Hook::hookFunction(
            fn() => parent::beginTransaction(...func_get_args()),
            func_get_args(),
        );
    }

    public function commitTransaction(int $timeoutMs)
    {
        return Hook::hookFunction(
            fn() => parent::commitTransaction(...func_get_args()),
            func_get_args(),
        );
    }

    public function abortTransaction(int $timeoutMs)
    {
        return Hook::hookFunction(
            fn() => parent::abortTransaction(...func_get_args()),
            func_get_args(),
        );
    }
}
