<?php

use DI\Container;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

return [
    Logger::class => static function(Container $c) {
        return new Logger('Interceptor', [
            new ChromePHPHandler(),
            new StreamHandler('php://stderr'),
        ]);
    },
];