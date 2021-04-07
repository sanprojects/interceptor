<?php

use DI\Container;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

return [
    Logger::class => static function(Container $c) {
        return new Logger('interceptor', [
            new ChromePHPHandler(),
            new ErrorLogHandler(),
            new StreamHandler('php://stderr'),
        ]);
    },
];