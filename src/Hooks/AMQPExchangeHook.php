<?php

namespace Sanprojects\Interceptor\Hooks;

require_once('AMQPExchangeWrapper.php'); // load before, to prevent injection into this file

class AMQPExchangeHook extends Hook
{
    protected const PATTERNS = [
        '@new\s+\\\?AMQPExchange\W*\(@' => 'new \\' . AMQPExchangeWrapper::class . '(',
        '@extends\s+\\\?AMQPExchange\b@' => 'extends \\' . AMQPExchangeWrapper::class,
    ];
}
