<?php

namespace Sanprojects\Interceptor;

use DI\Container;
use DI\ContainerBuilder;
use DI\Definition\Helper\DefinitionHelper;

class Di
{
    private static Container $container;

    public static function get($name)
    {
        return self::getDefault()->get($name);
    }

    /**
     * Define an object or a value in the container.
     *
     * @param string $name Entry name
     * @param mixed|DefinitionHelper $value Value, use definition helpers to define objects
     */
    public static function set($name, $value)
    {
        return self::getDefault()->set($name, $value);
    }

    public static function getDefault(): Container
    {
        return self::$container ?? self::build();
    }

    private static function build(): Container
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->addDefinitions(__DIR__ . '/diConfig.php');
        self::$container = $builder->build();

        return self::$container;
    }
}