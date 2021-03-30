<?php

namespace Sanprojects\Interceptor;

use DI\Container;
use DI\ContainerBuilder;

class Di
{
    private static Container $container;

    public static function get($name)
    {
        return self::getDefault()->get($name);
    }

    public static function getDefault(): Container
    {
        return self::$container ?? self::build();
    }

    private static function build(): Container
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->useAnnotations(false);
        $builder->addDefinitions(__DIR__ . '/diConfig.php');
        self::$container = $builder->build();

        return self::$container;
    }
}