<?php

namespace Sanprojects\Interceptor\Hooks;

require_once('AMQPConnectionWrapper.php'); // load before, to prevent injection into this file

class AMQPConnectionHook extends Hook
{
    protected const PATTERNS = [
        '@new\s+\\\?AMQPConnection\W*\(@' => 'new \\' . AMQPConnectionWrapper::class . '(',
        '@extends\s+\\\?AMQPConnection\b@' => 'extends \\' . AMQPConnectionWrapper::class,
    ];
}
