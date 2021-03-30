<?php

namespace Sanprojects\Interceptor\Hooks;

use PDO;

class PDOWrapper extends PDO
{
    public function exec($statement)
    {
        return PDOHook::hookFunction(['parent', __FUNCTION__], func_get_args());
    }

    public function prepare($statement, $options = NULL)
    {
        return PDOHook::hookFunction(['parent', __FUNCTION__], func_get_args());
    }
}
