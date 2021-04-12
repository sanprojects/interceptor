<?php

namespace Sanprojects\Interceptor\Hooks;

class PDO extends \PDO
{
    public function __construct($dsn, $user = null, $password = null, array $options = null)
    {
        Hook::hookFunction(
            fn() => parent::__construct(...func_get_args()),
            func_get_args(),
            [],
            'PDO::__construct'
        );

        $this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [PDOStatement::class]);
    }

    public function query(...$args)
    {
        return PDOHook::hookFunction(fn() => parent::query(...func_get_args()), func_get_args(), [], 'PDO::query');
    }
}
