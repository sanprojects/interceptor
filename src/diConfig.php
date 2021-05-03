<?php

use DI\Container;
use Monolog\Logger;
use Sanprojects\Interceptor\Logger\StdErrHandler;

return [
    Logger::class => static function(Container $c) {
        return new Logger('Interceptor', [
            new StdErrHandler(),
        ]);
    },
];