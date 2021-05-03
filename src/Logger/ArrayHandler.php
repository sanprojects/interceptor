<?php declare(strict_types=1);

namespace Sanprojects\Interceptor\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\TestHandler;

class ArrayHandler extends TestHandler
{
    public function __construct()
    {
        parent::__construct();
        $this->setFormatter(new LineFormatter(null, null, true,  true));
    }

    public function getLogs(): array
    {
        $result = [];
        foreach ($this->getRecords() as $record) {
            $row = [
                'time' => $record['datetime']->format("Y-m-d H:i:s.u"),
                'message' => $record['message'],
                'context' => $record['context'],
            ];
            if ($record['context']) {
                $row['context'] = $record['context'];
            }
            if ($record['extra']) {
                $row['extra'] = $record['extra'];
            }
            $result[] = $row;
        }

        return $result;
    }

    public function getFormattedLogs(): array
    {
        return array_column($this->getRecords(), 'formatted');
    }
}
