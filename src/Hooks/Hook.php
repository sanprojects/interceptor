<?php

namespace Sanprojects\Interceptor\Hooks;

use Sanprojects\Interceptor\Di;
use Sanprojects\Interceptor\Logger\Logger;

class Hook
{
    protected const PATTERNS = [];
    protected const HOOKED_FUNCTIONS = [];
    protected const HOOKED_CLASSES = [];
    private static $disableHook = false;

    /**
     * Filters the provided code to apply hooks.
     *
     * @param string $code The code to be filtered.
     * @return string The filtered code.
     */
    public function filter(string $code): string 
    {
        // don't intercept hooks
        if ($this->getNamespace($code) === __NAMESPACE__) {
            return $code;
        }

        $patterns = static::PATTERNS;
        foreach (static::HOOKED_FUNCTIONS as $func) {
            $patterns['/(?<!::|->|\w_|function\s)\\\?' . $func . '\s*\(/'] = '\\' . static::class . '::' . $func . '(';
        }

        foreach (static::HOOKED_CLASSES as $oldClass => $newClass) {
            $oldClassEscaped = preg_quote($oldClass, '/');
            $oldClassUse = $this->getClassUse($code, $oldClass);

            if ($oldClassUse && $oldClassUse !== $oldClass) {
                // don't replace "new Redis" if found "use Namespace/Redis;"
                continue;
            }

            $patterns['@new\s+\\\\?' . $oldClassEscaped . '\W*\(@'] = 'new \\' . $newClass . '(';
            $patterns['@extends\s+\\\\?' . $oldClassEscaped . '\b@'] = 'extends \\' . $newClass;

            $shortName = $this->getClassShortName($oldClass);
            if ($shortName && $oldClassUse) {
                $patterns['@new\s+\\\\?' . $shortName . '\W*\(@'] = 'new \\' . $newClass . '(';
                $patterns['@extends\s+\\\\?' . $shortName . '\b@'] = 'extends \\' . $newClass;
            }
        }

        return preg_replace(array_keys($patterns), array_values($patterns), $code);
    }

    /**
     * Retrieves the class use statement from the provided code.
     *
     * @param string $code The code to search for the class use statement.
     * @param string $class The class name to search for.
     * @return string The class use statement if found, otherwise an empty string.
     */
    public function getClassUse(string $code, string $class): string 
    {
        if (preg_match('@\buse\s+?([\\\\]*?' . preg_quote($class, '/') . ')\b@', $code, $matches)) {
            return $matches[1];
        }

        return preg_match('@\buse\s+?([\w\\\\]*?' . preg_quote($class, '/') . ')\b@', $code, $matches)
            ? $matches[1]
            : '';
    }

    /**
     * Retrieves the namespace from the provided code.
     *
     * @param string $code The code to search for the namespace.
     * @return string The namespace if found, otherwise an empty string.
     */
    public function getNamespace(string $code): string 
    {
        return preg_match('@\bnamespace\s+?([\w\\\\]+?);@', $code, $matches)
            ? $matches[1]
            : '';
    }

    /**
     * Retrieves the short name of the class from its full name.
     *
     * @param string $classFullName The full name of the class.
     * @return string The short name of the class.
     */
    public function getClassShortName(string $classFullName): string 
    {
        $classParts = explode('\\', $classFullName);

        return $classParts > 1 ? end($classParts) : '';
    }

    /**
     * Logs the provided message and data.
     *
     * @param string $message The message to be logged.
     * @param mixed $data The data to be logged.
     */
    public static function log(string $message, $data = []): void 
    {
        Di::get(Logger::class)->debug($message, $data);
    }

    /**
     * Hooks a function call and logs the result.
     *
     * @param callable $callable The function to be hooked.
     * @param array $args The arguments to be passed to the function.
     * @param array $extra Additional data to be logged.
     * @param string $name The name of the function.
     * @return mixed The result of the function call.
     * @throws \Exception If the function call throws an exception.
     */
    public static function hookFunction($callable, array $args, array $extra = [], $name = '') 
    {
        // prevent hook inside another hook
        if (self::$disableHook) {
            return call_user_func_array($callable, $args);
        }

        self::$disableHook = true;
        $funcName = $name ?: self::getCallableName($callable);
        try {
            $result = call_user_func_array($callable, $args);
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

    /**
     * Handles static method calls and hooks the function call.
     *
     * @param string $name The name of the method.
     * @param array $args The arguments to be passed to the method.
     * @return mixed The result of the method call.
     */
    public static function __callStatic($name, $args) 
    {
        return self::hookFunction($name, $args);
    }

    /**
     * Processes the result of a function call.
     *
     * @param mixed $result The result of the function call.
     * @return mixed The processed result.
     */
    public static function performResult($result) 
    {
        return $result;
    }

    /**
     * Retrieves the name of the callable.
     *
     * @param callable $callable The callable to retrieve the name from.
     * @return string The name of the callable.
     */
    static function getCallableName($callable): string 
    {
        if (is_string($callable)) {
            return trim($callable);
        }

        if (is_array($callable)) {
            if (is_object($callable[0])) {
                return sprintf('%s::%s', get_class($callable[0]), trim($callable[1]));
            }

            return sprintf('%s::%s', trim($callable[0]), trim($callable[1]));
        }

        if (is_object($callable)) {
            return get_class($callable);
        }

        return 'unknown';
    }
}
