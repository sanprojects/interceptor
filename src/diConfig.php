<?php

use DI\Container;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Sanprojects\Interceptor\LineFormatter;

return [
    Logger::class => static function(Container $c) {
        return new Logger('Interceptor', [
            new ChromePHPHandler(),
            (new StreamHandler('php://stderr'))
                ->setFormatter(new LineFormatter())
        ]);
    },
];