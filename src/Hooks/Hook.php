<?php

namespace Sanprojects\Interceptor\Hooks;

use Monolog\Logger;
use Sanprojects\Interceptor\Di;

class Hook
{
    protected const PATTERNS = [];

    protected const HOOKED_FUNCTIONS = [];

    public function filter(string $code): string
    {
        $patterns = static::PATTERNS;
        foreach (static::HOOKED_FUNCTIONS as $func) {
            $patterns['/(?<!::|->|\w_)\\\?' . $func . '\s*\(/i'] = '\\' . static::class . '::' . $func . '(';
        }

        return preg_replace(array_keys($patterns), array_values($patterns), $code);
    }

    /**
     * {@inheritdoc}
     */
    public static function log(string $message, $data = []): void
    {
        Di::get(Logger::class)->debug($message, $data);
    }

    /**
     * {@inheritdoc}
     */
    public static function hookFunction(callable $name, array $args)
    {
        $result = call_user_func_array($name, $args);
        self::log("$name()=" . $result, $args);

        return $result;
    }

    public static function __callStatic($name, $args) {
        return self::hookFunction($name, $args);
    }
}
