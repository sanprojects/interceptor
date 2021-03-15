<?php

namespace Sanprojects\Interceptor\Hooks;

use PDO;
use PDOStatement;
use Sanprojects\Interceptor\Logger;

class PDOStatementHook extends PDOStatement
{
    protected const PATTERNS = [
        '/new PDOStatementHook\(/i' => 'new '. __CLASS__ . '(',
        '/extends PDOStatementHook\b/' => 'extends \\' . __CLASS__,
    ];

    public static function filter(string $code): string
    {
        return preg_replace(array_keys(static::PATTERNS), array_values(static::PATTERNS), $code);
    }

    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
    {
        $result = parent::bindValue(...func_get_args());
        Logger::debug("PDOStatementHook::bindValue($parameter, $value)");

        return $result;
    }

    public function execute($input_parameters = null)
    {
        $result = parent::execute(...func_get_args());
        Logger::debug("PDOStatementHook::execute()");

        return $result;
    }
}
