<?php

namespace Sanprojects\Interceptor\Hooks\RdKafka;

use RdKafka\Consumer as RdKafkaConsumer;
use RdKafka\TopicConf;
use Sanprojects\Interceptor\Hooks\Hook;

class Consumer extends RdKafkaConsumer
{
    public function __construct($conf = null)
    {
        Hook::hookFunction(
            fn() => parent::__construct(...func_get_args()),
            func_get_args(),
            [],
            'RdKafka\Consumer::__construct'
        );
    }

    public function newTopic($topic_name, ?TopicConf $topic_conf = null)
    {
        return Hook::hookFunction(
            fn() => parent::newTopic(...func_get_args()),
            func_get_args(),
        );
    }

    public function newQueue()
    {
        return Hook::hookFunction(
            fn() => parent::newQueue(),
            func_get_args(),
        );
    }
}
