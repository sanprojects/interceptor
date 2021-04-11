<?php

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Sanprojects\Interceptor\Di;
use Sanprojects\Interceptor\Interceptor;

require __DIR__ . '/../vendor/autoload.php';

// clear logger handlers
Di::get(Logger::class)->setHandlers([]);

// intercept newly included files
Interceptor::interceptAll();