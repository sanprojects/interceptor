<?php

namespace Sanprojects\Interceptor\Hooks;

// load before, to prevent injection into this file
require_once('Redis.php');
require_once('PredisClient.php');

class RedisHook extends Hook
{
    protected const HOOKED_CLASSES = [
        \Redis::class => Redis::class,
        \Predis\Client::class => PredisClient::class,
    ];
}
