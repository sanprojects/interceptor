<?php

declare(strict_types=1);

namespace Sanprojects\Interceptor\Logger;

use DateTimeImmutable;

class Logger
{
    public function __construct(
        private string $title = '',
        private array $handlers = [],
        private $maxLineLength = 100000,
    ) {}

    public function debug(string $message, array $data = []): void
    {
        $formatedMessage = '[' . (new DateTimeImmutable())->format('Y-m-d\TH:i:s.u\Z') . '] '
            . $this->title . '.DEBUG: '
            . $message . ' '
            . $this->convertToString($data);

        foreach ($this->handlers as $handler) {
            $handler->handle($formatedMessage);
        }
    }

    public function setMaxLineLength(int $maxLineLength): void
    {
        $this->maxLineLength = $maxLineLength;
    }

    protected function convertToString($data): string
    {
        $result = $this->_convertToString($data);

        return mb_strlen($result) > $this->maxLineLength
            ? mb_substr($result, 0, $this->maxLineLength) . '...'
            : $result;
    }

    protected function _convertToString($data): string
    {
        if ($data === null || is_bool($data)) {
            return var_export($data, true);
        }

        if (is_scalar($data)) {
            return (string) $data;
        }

        if (is_array($data)) {
            return implode(' ', array_map(fn($v) => $this->toString($v), $data));
        }

        return json_encode($data);
    }

    protected function toString($data): string
    {
        if ($data === null || is_bool($data)) {
            return var_export($data, true);
        }

        if (is_scalar($data)) {
            return (string) $data;
        }

        if (is_resource($data)) {
            return (string) $data;
        }

        return json_encode($data);
    }
}
