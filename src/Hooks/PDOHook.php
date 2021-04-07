<?php

namespace Sanprojects\Interceptor\Hooks;

require_once('PDOWrapper.php'); // load before, to prevent injection into this file

class PDOHook extends Hook
{
    protected const PATTERNS = [
        '@new\s+\\\?PDO\W*\(@' => 'new \\' . PDOWrapper::class . '(',
        '@extends\s+\\\?PDO\b@' => 'extends \\' . PDOWrapper::class,
    ];

    protected const HOOKED_FUNCTIONS = [
        'mysqli_connect',
        'mysqli_query',
    ];
}
