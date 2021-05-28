<?php

use DI\Container;
use Monolog\Logger;
use Sanprojects\Interceptor\Logger\ArrayHandler;
use Sanprojects\Interceptor\Logger\StdErrHandler;

return [
    ArrayHandler::class => static function(Container $c) {
        return new ArrayHandler();
    },
    Logger::class => static function(Container $c) {
        return new Logger('Interceptor', [
            new StdErrHandler(),
            $c->get(ArrayHandler::class),
        ]);
    },
];