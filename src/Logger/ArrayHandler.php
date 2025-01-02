<?php

declare(strict_types=1);

namespace Sanprojects\Interceptor\Logger;

class ArrayHandler implements HandlerInterface
{
    private int $maxRecordsCount = 1000;
    private array $records = [];

    public function handle($formatedMessage): void
    {
        $this->records[] = $formatedMessage;
        $this->records = array_slice($this->records, -$this->maxRecordsCount);
    }

    public function getRecords(): array
    {
        return $this->records;
    }

    public function clearRecords(): void
    {
        $this->records = [];
    }
}
