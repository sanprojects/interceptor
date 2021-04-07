<?php

namespace Sanprojects\Interceptor\Hooks;

require_once('AMQPConnectionWrapper.php'); // load before, to prevent injection into this file

class AMQPHook extends Hook
{
    protected const PATTERNS = [
        '@new\s+\\\?AMQPConnection\W*\(@' => 'new \\' . AMQPConnectionWrapper::class . '(',
        '@extends\s+\\\?AMQPConnection\b@' => 'extends \\' . AMQPConnectionWrapper::class,
        '@new\s+\\\?AMQPExchange\W*\(@' => 'new \\' . AMQPExchangeWrapper::class . '(',
        '@extends\s+\\\?AMQPExchange\b@' => 'extends \\' . AMQPExchangeWrapper::class,
    ];
}
