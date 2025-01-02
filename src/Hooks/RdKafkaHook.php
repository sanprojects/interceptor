<?php

namespace Sanprojects\Interceptor\Hooks;

class RdKafkaHook extends Hook
{
    protected const HOOKED_CLASSES = [
        \RdKafka\Producer::class => RdKafka\Producer::class,
        \RdKafka\Consumer::class => RdKafka\Consumer::class,
        \RdKafka\KafkaConsumer::class => RdKafka\KafkaConsumer::class,
    ];
}
