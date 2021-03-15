<?php

namespace Sanprojects\Interceptor\Hooks;

use PDO;
use Sanprojects\Interceptor\Logger;

class PdoHook extends PDO
{
    protected const PATTERNS = [
        '/new PDO\(/i' => 'new ' . __CLASS__ . '(',
        '/extends PDO\b/' => 'extends \\' . __CLASS__,
    ];

    public static function filter(string $code): string
    {
        return preg_replace(array_keys(static::PATTERNS), array_values(static::PATTERNS), $code);
    }

    public function exec($statement)
    {
        $result = parent::exec($statement);
        Logger::debug("exec()=$result");

        return $result;
    }

    public function prepare($statement, $options = NULL)
    {
        $result = parent::prepare($statement, $options);
        Logger::debug("PDO::prepare($statement)");

        return $result;
    }
}
