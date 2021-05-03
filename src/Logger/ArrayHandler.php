<?php declare(strict_types=1);

namespace Sanprojects\Interceptor\Logger;

use Monolog\Handler\TestHandler;

class ArrayHandler extends TestHandler
{
    public function getLogs(): array
    {
        $result = [];
        foreach ($this->getRecords() as $record) {
            $result[] = [
                'datetime' => $record['datetime']['date'] ?? '',
                'context' => $record['context'],
                'message' => $record['message'],
            ];
        }

        return $result;
    }

    public function getFormattedLogs(): array
    {
        return array_column($this->getRecords(), 'formatted');
    }
}
