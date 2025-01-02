<?php

namespace Sanprojects\Interceptor\Hooks\RdKafka;

use Sanprojects\Interceptor\Hooks\Hook;

class RdKafkaHook extends Hook
{
    protected const HOOKED_CLASSES = [
        \RdKafka\Producer::class => Producer::class,
        \RdKafka\Consumer::class => Consumer::class,
        \RdKafka\KafkaConsumer::class => KafkaConsumer::class,
    ];
}
