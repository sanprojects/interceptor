<?php

namespace Sanprojects\Interceptor\Hooks;

// load before, to prevent injection into this file
require_once('Redis.php');

class RedisHook extends Hook
{
    protected const HOOKED_CLASSES = [
        'Redis' => Redis::class,
        //'Client' => PredisClient::class,
    ];
}
