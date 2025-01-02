<?php

namespace Sanprojects\Interceptor\Hooks\RdKafka;

use Sanprojects\Interceptor\Hooks\Hook;

class Producer extends \RdKafka\Producer
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

    public function addBrokers($broker_list)
    {
        return Hook::hookFunction(
            fn() => parent::addBrokers(...func_get_args()),
            func_get_args(),
            [],
            'RdKafka\Producer::addBrokers'
        );
    }

    public function newTopic($topic_name, $topic_conf = null)
    {
        return Hook::hookFunction(
            fn() => parent::newTopic(...func_get_args()),
            func_get_args(),
            [],
            'RdKafka\Producer::newTopic'
        );
    }
}
