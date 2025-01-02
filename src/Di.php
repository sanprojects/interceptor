<?php

namespace Sanprojects\Interceptor;

use Sanprojects\Interceptor\Logger\ArrayHandler;
use Sanprojects\Interceptor\Logger\Logger;
use Sanprojects\Interceptor\Logger\StdErrHandler;

class Di
{
    private static array $definitions = [];
    private static array $container = [];

    public static function get($name)
    {
        self::build();

        return self::$container[$name] = self::$container[$name] ?? self::$definitions[$name]();
    }

    public static function set($name, $value): void
    {
        self::$definitions[$name] = $value;
    }

    private static function build(): void
    {
        static $isBuilt = false;

        if ($isBuilt) {
            return;
        }

        $isBuilt = true;

        self::set(ArrayHandler::class, static fn() => new ArrayHandler());

        self::set(Logger::class, static fn() => new Logger('Interceptor', [
            new StdErrHandler(),
            self::get(ArrayHandler::class),
        ]));
    }
}
