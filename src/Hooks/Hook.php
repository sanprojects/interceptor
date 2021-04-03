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

    public static function hookFunction($name, array $args)
    {
        $funcName = self::getCallableName($name);
        try {
            $result = call_user_func_array($name, $args);
        } catch (\Exception $e) {
            self::log($funcName, ['args' => $args, 'result' => $e->getMessage()]);
            throw $e;
        }

        self::log($funcName, ['args' => $args, 'result' => self::performResult($result)]);

        return $result;
    }

    public static function __callStatic($name, $args) {
        return self::hookFunction($name, $args);
    }

    public static function performResult($result) {
        return var_export($result, true);
    }

    static function getCallableName($callable): string
    {
        if (is_string($callable)) {
            return trim($callable);
        }

        if (is_array($callable)) {
            if (is_object($callable[0])) {
                return sprintf("%s::%s", get_class($callable[0]), trim($callable[1]));
            }

            return sprintf("%s::%s", trim($callable[0]), trim($callable[1]));
        }

        if ($callable instanceof Closure) {
            return 'closure';
        }

        return 'unknown';
    }
}
