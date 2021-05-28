<?php

use Monolog\Logger;
use Sanprojects\Interceptor\Di;
use Sanprojects\Interceptor\Interceptor;

require __DIR__ . '/../vendor/autoload.php';

Di::set('config', [
    'mysql' => [
        'host' => 'ensembldb.ensembl.org',
        'username' => 'anonymous',
        'password' => '',
    ],
]);

// intercept newly included files
Interceptor::interceptAll();