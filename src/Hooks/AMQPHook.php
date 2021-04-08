<?php

namespace Sanprojects\Interceptor\Hooks;

class AMQPHook extends Hook
{
    protected const PATTERNS = [
        '@new\s+\\\?AMQPConnection\W*\(@' => 'new \\' . AMQPConnection::class . '(',
        '@extends\s+\\\?AMQPConnection\b@' => 'extends \\' . AMQPConnection::class,
        '@new\s+\\\?AMQPExchange\W*\(@' => 'new \\' . AMQPExchange::class . '(',
        '@extends\s+\\\?AMQPExchange\b@' => 'extends \\' . AMQPExchange::class,
    ];
}
