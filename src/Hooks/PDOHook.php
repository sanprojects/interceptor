<?php

namespace Sanprojects\Interceptor\Hooks;

class PDOHook extends Hook
{
    protected const PATTERNS = [
        '@new\s+\\\?PDO\W*\(@i' => 'new ' . PDOWrapper::class . '(',
        '@extends\s+\\\?PDO\b@i' => 'extends \\' . PDOWrapper::class,
        '@new\s+\\\?PDOStatementHook\W*\(@i' => 'new ' . PDOStatementWrapper::class . '(',
        '@extends\s+\\\?PDOStatementHook\b@i' => 'extends \\' . PDOStatementWrapper::class,
    ];
}
