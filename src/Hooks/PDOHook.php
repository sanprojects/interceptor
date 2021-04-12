<?php

namespace Sanprojects\Interceptor\Hooks;

class PDOHook extends Hook
{
    protected const HOOKED_CLASSES = [
        \PDO::class => PDO::class,
        \PDOStatement::class => PDOStatement::class,
    ];
}
