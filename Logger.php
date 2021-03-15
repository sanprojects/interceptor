<?php

namespace Sanprojects\Interceptor;

use Monolog\Handler\ChromePHPHandler;
use Psr\Log\LoggerInterface;

class Logger
{
    protected static $logs = [];

    protected static $logger;

    /**
     * Creates logger instance
     *
     * @return \Psr\Log\LoggerInterface
     */
    public static function getLogger(): LoggerInterface
    {
        return self::$logger ?: new \Monolog\Logger(
            'interceptor',
            [new ChromePHPHandler()],
            []
        );
    }

    public static function debug(string $message): void
    {
        //self::$logs[] = $message;

        //self::getLogger()->debug($message);
        $message = str_replace("\n", '↵' , $message);
        print_r($message . "\n");
    }

    public static function getLogs()
    {
        return self::$logs;
    }
}
