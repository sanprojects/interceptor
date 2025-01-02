<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use RdKafka\Conf;
use RdKafka\Message;
use Sanprojects\Interceptor\Di;
use Sanprojects\Interceptor\Logger\ArrayHandler;
use Sanprojects\Interceptor\Hooks\RdKafka\Producer;
use Sanprojects\Interceptor\Hooks\RdKafka\Consumer;
use Sanprojects\Interceptor\Hooks\RdKafka\KafkaConsumer;

/**
 * @internal
 */
#[PHPUnit\Framework\Attributes\CoversNothing]
final class RdKafkaTest extends TestCase
{
    protected function setUp(): void
    {
        Di::get(ArrayHandler::class)->clearRecords();
    }

    protected function getLogs(): array
    {
        return Di::get(ArrayHandler::class)->getRecords();
    }

    public function testProducer(): void
    {
        $conf = new Conf();
        $producer = new Producer($conf);
        $producer->produce('test_topic', 0, RD_KAFKA_MSG_F_BLOCK, 'test_payload');
        $producer->flush(1000);

        $logs = $this->getLogs();
        self::assertStringContainsString('RdKafka\Producer::__construct', $logs[0]);
        self::assertStringContainsString('RdKafka\Producer::produce', $logs[1]);
        self::assertStringContainsString('RdKafka\Producer::flush', $logs[2]);
    }

    public function testConsumer(): void
    {
        $conf = new Conf();
        $consumer = new Consumer($conf);
        $message = $consumer->consume(1000);
        $consumer->commit($message);

        $logs = $this->getLogs();
        self::assertStringContainsString('RdKafka\Consumer::__construct', $logs[0]);
        self::assertStringContainsString('RdKafka\Consumer::consume', $logs[1]);
        self::assertStringContainsString('RdKafka\Consumer::commit', $logs[2]);
    }

    public function testKafkaConsumer(): void
    {
        $conf = new Conf();
        $kafkaConsumer = new KafkaConsumer($conf);
        $message = $kafkaConsumer->consume(1000);
        $kafkaConsumer->commit($message);

        $logs = $this->getLogs();
        self::assertStringContainsString('RdKafka\KafkaConsumer::__construct', $logs[0]);
        self::assertStringContainsString('RdKafka\KafkaConsumer::consume', $logs[1]);
        self::assertStringContainsString('RdKafka\KafkaConsumer::commit', $logs[2]);
    }
}
