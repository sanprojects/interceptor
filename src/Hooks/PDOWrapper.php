<?php

namespace Sanprojects\Interceptor\Hooks;

use PDO;

class PDOWrapper extends PDO
{
    public function exec($statement)
    {
        return PDOHook::hookFunction(['self', __FUNCTION__], func_get_args());
    }

    public function query($statement, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, $arg3 = null, array $ctorargs = array())
    {
        return PDOHook::hookFunction(array($this, 'parent::' . __FUNCTION__), func_get_args());
    }

    public function prepare($statement, $options = NULL)
    {
        return PDOHook::hookFunction(['self', __FUNCTION__], func_get_args());
    }
}
