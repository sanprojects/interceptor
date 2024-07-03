<?php

namespace Sanprojects\Interceptor\Logger;

interface HandlerInterface
{
    public function handle($formatedMessage): void;
}