#!/usr/bin/env php
<?php

use Sanprojects\Interceptor\Interceptor;

spl_autoload_register(function ($class): void {
    $prefix = 'Sanprojects\Interceptor';
    $baseDir = __DIR__ . '/src/';

    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

Interceptor::interceptAll();

// load php file from command line
if (isCliApp()) {
    $argv = $_SERVER['argv'] = array_slice($argv, 1);
    include $argv[0];
}

function isCliApp(): bool
{
    $appName = $_SERVER['argv'][0] ?? '';

    return str_contains($appName, 'interceptor.ph');
}
