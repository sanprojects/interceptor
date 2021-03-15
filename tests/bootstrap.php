<?php

use Sanprojects\Interceptor\Interceptor;

require __DIR__ . '/../vendor/autoload.php';

// intercept newly included files
Interceptor::interceptAll();