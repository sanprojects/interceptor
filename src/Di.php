<?php

namespace Sanprojects\Interceptor;

use DI\Container;
use DI\ContainerBuilder;
use DI\Definition\Helper\DefinitionHelper;
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

        self::set(ArrayHandler::class, function() {
            return new ArrayHandler();
        });

        self::set(Logger::class, function() {
            return new Logger('Interceptor', [
                new StdErrHandler(),
                self::get(ArrayHandler::class)
            ]);
        });
    }
}