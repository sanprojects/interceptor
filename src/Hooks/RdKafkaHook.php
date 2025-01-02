<?php

namespace Sanprojects\Interceptor\Hooks;

class RdKafkaHook extends Hook
{
    protected const HOOKED_CLASSES = [
        \RdKafka\Producer::class => RdKafkaProducer::class,
        \RdKafka\Consumer::class => RdKafkaConsumer::class,
        \RdKafka\KafkaConsumer::class => RdKafkaKafkaConsumer::class,
    ];
}
