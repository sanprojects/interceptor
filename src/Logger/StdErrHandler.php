<?php

declare(strict_types=1);

namespace Sanprojects\Interceptor\Logger;

class StdErrHandler implements HandlerInterface
{
    public function handle($formatedMessage): void
    {
        fwrite(\STDERR, $formatedMessage . \PHP_EOL);
    }
}
