<?php

namespace Sanprojects\Interceptor\Hooks;

class MysqliHook extends Hook
{
    protected const HOOKED_FUNCTIONS = [
        'mysqli_connect',
        'mysqli_query',
    ];

    protected static array $connections = [];

    public static function mysqli_connect($host = null, $user = null, $password = null, $database = null, $port = null, $socket = null)
    {
        $result = self::hookFunction(__FUNCTION__, func_get_args());

        if ($result) {
            self::$connections[spl_object_id($result)] = $host;
        }

        return $result;
    }

    public static function mysqli_query($link, $query, $resultmode = MYSQLI_STORE_RESULT)
    {
        $connection = self::$connections[spl_object_id($link)] ?? '';

        return self::hookFunction(__FUNCTION__, func_get_args(), [$connection, $query]);
    }
}
