<?php declare(strict_types=1);

namespace Sanprojects\Interceptor;

class LineFormatter extends \Monolog\Formatter\LineFormatter
{
    protected function convertToString($data): string
    {
        if (null === $data || is_bool($data)) {
            return var_export($data, true);
        }

        if (is_scalar($data)) {
            return (string) $data;
        }

        if (is_array($data)) {
            return implode(' ', array_map(fn($v) => $this->toString($v), $data));
        }

        return $this->toJson($data, true);
    }

    protected function toString($data): string
    {
        if (null === $data || is_bool($data)) {
            return var_export($data, true);
        }

        if (is_scalar($data)) {
            return (string) $data;
        }

        return $this->toJson($data, true);
    }
}
