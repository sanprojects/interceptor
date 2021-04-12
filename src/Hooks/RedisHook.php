<?php

namespace Sanprojects\Interceptor\Hooks;

class RedisHook extends Hook
{
    protected const HOOKED_CLASSES = [
        \Redis::class => Redis::class,
        \Predis\Client::class => PredisClient::class,
    ];
}
