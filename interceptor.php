#!/usr/bin/env php
<?php

namespace Sanprojects\Interceptor;

include $_composer_autoload_path
    ?? __DIR__ . '/vendor/autoload.php';

Interceptor::interceptAll();
