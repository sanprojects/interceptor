<?php

namespace Sanprojects\Interceptor\Hooks;

class RdKafkaHook extends Hook
{
    protected const HOOKED_CLASSES = [
        \RdKafka\Producer::class => \Sanprojects\Interceptor\Hooks\RdKafka\Producer::class,
        \RdKafka\Consumer::class => \Sanprojects\Interceptor\Hooks\RdKafka\Consumer::class,
        \RdKafka\KafkaConsumer::class => \Sanprojects\Interceptor\Hooks\RdKafka\KafkaConsumer::class,
    ];
}
