<?php

namespace Sanprojects\Interceptor\Hooks;

class Hook
{
    protected const PATTERNS = [];

    protected const HOOKED_FUNCTIONS = [];

    /**
     * {@inheritdoc}
     */
    public static function filter(string $code): string
    {
        $patterns = static::PATTERNS;
        foreach (static::HOOKED_FUNCTIONS as $func) {
            $patterns['/(?<!::|->|\w_)\\\?' . $func . '\s*\(/i'] = '\\' . static::class . '::' . $func . '(';
        }

        return preg_replace(array_keys($patterns), array_values($patterns), $code);
    }
}
