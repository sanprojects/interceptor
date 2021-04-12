<?php

namespace Sanprojects\Interceptor\Hooks;

use Monolog\Logger;
use Sanprojects\Interceptor\Di;

class Hook
{
    protected const PATTERNS = [];
    protected const HOOKED_FUNCTIONS = [];
    protected const HOOKED_CLASSES = [];
    private static $disableHook = false;

    public function filter(string $code): string
    {
        $patterns = static::PATTERNS;
        foreach (static::HOOKED_FUNCTIONS as $func) {
            $patterns['/(?<!::|->|\w_)\\\?' . $func . '\s*\(/'] = '\\' . static::class . '::' . $func . '(';
        }

        foreach (static::HOOKED_CLASSES as $oldClass => $newClass) {
            $oldClassEscaped = preg_quote($oldClass, '/');
            $oldClassUse = $this->getClassUse($code, $oldClass);

            if ($oldClassUse && $oldClassUse !== $oldClass) {
                // don't replace "new Redis" if found "use Namespace/Redis;"
                continue;
            }

            $patterns['@new\s+\\\?' . $oldClassEscaped . '\W*\(@'] = 'new \\' . $newClass . '(';
            $patterns['@extends\s+\\\?' . $oldClassEscaped . '\b@'] = 'extends \\' . $newClass;

            $shortName = $this->getClassShortName($oldClass);
            if ($shortName && $oldClassUse) {
                $patterns['@new\s+\\\?' . $shortName . '\W*\(@'] = 'new \\' . $newClass . '(';
                $patterns['@extends\s+\\\?' . $shortName . '\b@'] = 'extends \\' . $newClass;
            }
        }

        return preg_replace(array_keys($patterns), array_values($patterns), $code);
    }

    public function getClassUse(string $code, string $class): string
    {
        return preg_match('@\buse\s+?([\w\\\]*?' . preg_quote($class, '/') . ')\b@', $code, $matches)
            ? $matches[1]
            : '';
    }

    public function getClassShortName(string $classFullName): string
    {
        $classParts = explode('\\', $classFullName);

        return $classParts > 1 ? end($classParts) : '';
    }

    /**
     * {@inheritdoc}
     */
    public static function log(string $message, $data = []): void
    {
        Di::get(Logger::class)->debug($message, $data);
    }

    public static function hookFunction($callble, array $args, array $extra = [], $name = '')
    {
        // prevent hook inside another hook
        if (self::$disableHook) {
            return call_user_func_array($callble, $args);
        }

        self::$disableHook = true;
        $funcName = $name ?: self::getCallableName($callble);
        try {
            $result = call_user_func_array($callble, $args);
        } catch (\Exception $e) {
            $args = $extra ?: $args;
            $args[] = $e->getMessage();
            self::log($funcName, $args);
            throw $e;
        }

        $args = $extra ?: $args;
        $args[] = self::performResult($result);
        self::log($funcName, $args);
        self::$disableHook = false;

        return $result;
    }

    public static function __callStatic($name, $args) {
        return self::hookFunction($name, $args);
    }

    public static function performResult($result) {
        return $result;
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

        if (is_object($callable)) {
            return get_class($callable);
        }

        return 'unknown';
    }
}
