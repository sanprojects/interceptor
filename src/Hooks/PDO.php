<?php

namespace Sanprojects\Interceptor\Hooks;

class PDO extends \PDO
{
    public function __construct($dsn, $user = null, $password = null, array $options = null)
    {
        parent::__construct($dsn, $user, $password, $options);
        $this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [PDOStatement::class]);
    }

    public function query($statement, $mode = \PDO::ATTR_DEFAULT_FETCH_MODE, $arg3 = null, array $ctorargs = array())
    {
        return PDOHook::hookFunction(fn() => parent::query(...func_get_args()), func_get_args());
    }
}
