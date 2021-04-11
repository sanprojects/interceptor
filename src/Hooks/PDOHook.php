<?php

namespace Sanprojects\Interceptor\Hooks;

// load before, to prevent injection into this file
require_once('PDO.php');
require_once('PDOStatement.php');

class PDOHook extends Hook
{
    protected const HOOKED_CLASSES = [
        \PDO::class => PDO::class,
        \PDOStatement::class => PDOStatement::class,
    ];
}
