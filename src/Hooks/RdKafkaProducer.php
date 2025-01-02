<?php

namespace Sanprojects\Interceptor\Hooks;

class RdKafkaProducer extends \RdKafka\Producer
{
    public function produce($topic, $partition, $msgflags, $payload, $key = null, $headers = null)
    {
        return Hook::hookFunction(
            fn() => parent::produce($topic, $partition, $msgflags, $payload, $key, $headers),
            func_get_args(),
            [],
            'RdKafka\Producer::produce'
        );
    }
}
