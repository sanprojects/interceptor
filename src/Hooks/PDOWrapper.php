<?php

namespace Sanprojects\Interceptor\Hooks;

use PDO;

class PDOWrapper extends PDO
{
    public function exec($statement)
    {
        return PDOHook::hookFunction([$this, 'parent::' . __FUNCTION__], func_get_args());
    }

    public function query()
    {
        return PDOHook::hookFunction([$this, 'parent::' . __FUNCTION__], func_get_args());
    }

    public function prepare($statement, $options = NULL)
    {
        return PDOHook::hookFunction(fn() => parent::prepare(...func_get_args()), func_get_args());
    }
}
