<?php

namespace Sanprojects\Interceptor\Hooks;

// load before, to prevent injection into this file
require_once('PDO.php');
require_once('PDOStatement.php');

class PDOHook extends Hook
{
    protected const PATTERNS = [
        '@new\s+\\\?PDO\W*\(@' => 'new \\' . PDO::class . '(',
        '@extends\s+\\\?PDO\b@' => 'extends \\' . PDO::class,
        '@new\s+\\\?PDOStatement\W*\(@' => 'new \\' . PDOStatement::class . '(',
        '@extends\s+\\\?PDOStatement\b@' => 'extends \\' . PDOStatement::class,
    ];

    protected const HOOKED_FUNCTIONS = [
        'mysqli_connect',
        'mysqli_query',
    ];
}
