<?php declare(strict_types=1);

namespace Sanprojects\Interceptor;

class LineFormatter extends \Monolog\Formatter\LineFormatter
{
    private int $maxLineLength = 500;

    protected function convertToString($data): string
    {
        $result = $this->_convertToString($data);

        return mb_strlen($result) > $this->maxLineLength
            ? mb_substr($result, 0, $this->maxLineLength) . '...'
            : $result;
    }

    protected function _convertToString($data): string
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
