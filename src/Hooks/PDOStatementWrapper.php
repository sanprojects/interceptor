<?php

namespace Sanprojects\Interceptor\Hooks;

use PDO;
use PDOStatement;

class PDOStatementWrapper extends PDOStatement
{
    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
    {
        return PDOHook::hookFunction(['parent', __FUNCTION__], func_get_args());
    }

    public function execute($input_parameters = null)
    {
        return PDOHook::hookFunction(['parent', __FUNCTION__], func_get_args());
    }
}
