<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Predis\Client;
use Sanprojects\Interceptor\Di;
use Sanprojects\Interceptor\Logger\ArrayHandler;

/**
 * @internal
 */
#[PHPUnit\Framework\Attributes\CoversNothing]
final class InterceptorTest extends TestCase
{
    protected function setUp(): void
    {
        Di::get(ArrayHandler::class)->clearRecords();
    }

    protected function getLogs(): array
    {
        return Di::get(ArrayHandler::class)->getRecords();
    }

    public function testStdInOut(): void
    {
        self::assertSame(12, fwrite(STDOUT, 'testStdInOut'));
        self::assertEmpty($this->getLogs());
    }

    public function testFileWrite(): void
    {
        $fileHandler = fopen(__DIR__ . '/test.txt', 'w+');
        self::assertNotEmpty($fileHandler);
        self::assertSame(4, fwrite($fileHandler, 'test'));
        fseek($fileHandler, 0);
        self::assertSame('test', fread($fileHandler, 100));
        fclose($fileHandler);
        $logs = $this->getLogs();
        self::assertStringContainsString('fopen', $logs[0]);
        self::assertStringContainsString('fwrite', $logs[1]);
        self::assertStringContainsString('test.txt', $logs[1]);
        self::assertStringContainsString('fread', $logs[2]);
    }

    public function testFileGetContents(): void
    {
        self::assertSame('test', file_get_contents(__DIR__ . '/test.txt'));
        $logs = $this->getLogs();
        self::assertStringContainsString('file_get_contents', $logs[0]);
    }

    public function testFilePutContents(): void
    {
        self::assertSame(4, file_put_contents(__DIR__ . '/test.txt', 'test'));
        $logs = $this->getLogs();
        self::assertStringContainsString('file_put_contents', $logs[0]);
        self::assertStringContainsString('test.txt', $logs[0]);
        self::assertStringContainsString('test', $logs[0]);
    }

    public function testRedis(): void
    {
        if (!class_exists('Redis')) {
            self::markTestSkipped('Redis not exists');
        }

        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->set('test', '{"jsonKey":123}');
        self::assertSame('{"jsonKey":123}', $redis->get('test'));

        $logs = $this->getLogs();
        self::assertStringContainsString('127.0.0.1', $logs[0]);
        self::assertStringContainsString('123', $logs[1]);
    }

    public function testPredis(): void
    {
        $redis = new Client();
        $redis->set('test', '{"jsonKey":123}');
        self::assertSame('{"jsonKey":123}', $redis->get('test'));

        $logs = $this->getLogs();
        self::assertStringContainsString('Redis::__construct', $logs[0]);
        self::assertStringContainsString('Redis tcp://127.0.0.1:6379 set test {"jsonKey":123}', $logs[1]);
        self::assertStringContainsString('Redis tcp://127.0.0.1:6379 get test {"jsonKey":123}', $logs[2]);
    }

    public function testMysqli(): void
    {
        $config = Di::get('config')['mysql'];
        $mysqli = mysqli_connect($config['host'], $config['username'], $config['password']);
        $query = mysqli_query($mysqli, 'SELECT 123');
        $return = mysqli_fetch_array($query);
        self::assertSame('123', $return[0]);

        $logs = $this->getLogs();
        self::assertStringContainsString('mysqli_connect', $logs[0]);
        self::assertStringContainsString('SELECT 123', $logs[1]);
    }

    public function testPDO(): void
    {
        $config = Di::get('config')['mysql'];
        $dbh = new PDO('mysql:dbname=;host=' . $config['host'], $config['username'], $config['password']);

        $stmt = $dbh->prepare('SELECT :test;');
        $stmt->bindValue(':test', 123, PDO::PARAM_INT);
        $stmt->execute();
        self::assertSame(123, $stmt->fetchColumn());

        $stmt = $dbh->prepare('SELECT :test;');
        $stmt->bindValue(':test', 123, PDO::PARAM_INT);
        $stmt->execute();
        self::assertSame([[123 => 123, 0 => 123]], $stmt->fetchAll());

        $stmt = $dbh->prepare('SELECT :test;');
        $stmt->bindValue(':test', 123, PDO::PARAM_INT);
        $stmt->execute();
        self::assertSame([123 => 123, 0 => 123], $stmt->fetch());

        $logs = $this->getLogs();
        self::assertStringContainsString('PDO::__construct', $logs[0]);
        self::assertStringContainsString('SELECT 123', $logs[1]);
    }

    public function testCurl(): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://httpbin.org/post');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'postvar1=value1&postvar2=value2&postvar3=value3');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);

        $logs = $this->getLogs();
        self::assertStringContainsString('headers', $server_output);
        self::assertStringContainsString('curl -vX POST', $logs[0]);
    }

    public function testRdKafkaProducer(): void
    {
        if (!class_exists('RdKafka\Producer')) {
            self::markTestSkipped('RdKafka\Producer not exists');
        }

        $conf = new RdKafka\Conf();
        $producer = new RdKafka\Producer($conf);
        $producer->addBrokers('localhost:9092');
        $topic = $producer->newTopic('test');
        self::assertNotNull($topic);

        $logs = $this->getLogs();
        self::assertStringContainsString('RdKafka\Producer::__construct', $logs[0]);
        self::assertStringContainsString('RdKafka\Producer::addBrokers', $logs[1]);
        self::assertStringContainsString('RdKafka\Producer::newTopic', $logs[2]);
    }

    public function testRdKafkaConsumer(): void
    {
        if (!class_exists('RdKafka\Consumer')) {
            self::markTestSkipped('RdKafka\Consumer not exists');
        }

        $conf = new RdKafka\Conf();
        $consumer = new RdKafka\Consumer($conf);
        $consumer->addBrokers('localhost:9092');
        $topic = $consumer->newTopic('test');
        self::assertNotNull($topic);

        $logs = $this->getLogs();
        self::assertStringContainsString('RdKafka\Consumer::__construct', $logs[0]);
        self::assertStringContainsString('RdKafka\Consumer::addBrokers', $logs[1]);
        self::assertStringContainsString('RdKafka\Consumer::newTopic', $logs[2]);
    }

    public function testRdKafkaKafkaConsumer(): void
    {
        if (!class_exists('RdKafka\KafkaConsumer')) {
            self::markTestSkipped('RdKafka\KafkaConsumer not exists');
        }

        $conf = new RdKafka\Conf();
        $conf->set('group.id', 'testGroup');
        $conf->set('metadata.broker.list', 'localhost:9092');
        $consumer = new RdKafka\KafkaConsumer($conf);
        $consumer->subscribe(['test']);
        $message = $consumer->consume(1000);
        self::assertNotNull($message);

        $logs = $this->getLogs();
        self::assertStringContainsString('RdKafka\KafkaConsumer::__construct', $logs[0]);
        self::assertStringContainsString('RdKafka\KafkaConsumer::subscribe', $logs[1]);
        self::assertStringContainsString('RdKafka\KafkaConsumer::consume', $logs[2]);
    }
}
