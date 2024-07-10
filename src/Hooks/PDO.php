<?php

namespace Sanprojects\Interceptor\Hooks;

class PDO extends \PDO
{
    public string $serverName = '';

    public function __construct($dsn, $user = null, $password = null, array $options = null)
    {
        $this->serverName = $dsn;

        Hook::hookFunction(
            fn() => parent::__construct(...func_get_args()),
            func_get_args(),
            [],
            'PDO::__construct'
        );

        $this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [PDOStatement::class]);
    }

    #[\ReturnTypeWillChange]
    public function prepare($query, $options = NULL)
    {
        $statement = parent::prepare(...func_get_args());
        $statement->pdo = $this;

        return $statement;
    }

    #[\ReturnTypeWillChange]
    public function exec(string $statement)
    {
        return PDOHook::hookFunction(fn() => parent::exec(...func_get_args()), func_get_args(), [], 'PDO::exec ' . $this->serverName);
    }

    #[\ReturnTypeWillChange]
    public function commit()
    {
        return PDOHook::hookFunction(fn() => parent::commit(...func_get_args()), func_get_args(), [], 'PDO::commit ' . $this->serverName);
    }

    #[\ReturnTypeWillChange]
    public function rollBack()
    {
        return PDOHook::hookFunction(fn() => parent::rollBack(...func_get_args()), func_get_args(), [], 'PDO::rollBack ' . $this->serverName);
    }
}
