#!/usr/bin/env php
<?php

namespace Sanprojects\Interceptor;

spl_autoload_register(function ($class) {
    $prefix = 'Sanprojects\\Interceptor';
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
