<?php

namespace Sanprojects\Interceptor\Hooks;

use PDO;

class PDOWrapper extends PDO
{
    public function __construct($dsn, $user = null, $password = null, array $options = null)
    {
        parent::__construct($dsn, $user, $password, $options);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, [PDOStatement::class]);
    }

    public function exec($statement)
    {
        return PDOHook::hookFunction(fn() => parent::prepare(...func_get_args()), func_get_args());
    }

    public function query($statement, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, $arg3 = null, array $ctorargs = array())
    {
        return PDOHook::hookFunction(fn() => parent::prepare(...func_get_args()), func_get_args());
    }

    public function prepare($statement, $options = NULL)
    {
        return PDOHook::hookFunction(fn() => parent::prepare(...func_get_args()), func_get_args());
    }
}
