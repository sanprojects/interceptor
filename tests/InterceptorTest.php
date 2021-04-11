<?php declare(strict_types=1);

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Sanprojects\Interceptor\Di;
use Sanprojects\Interceptor\LineFormatter;

final class InterceptorTest extends TestCase
{
    private TestHandler $testHandler;

    protected function setUp(): void
    {
        $this->testHandler = (new TestHandler())
            ->setFormatter(new LineFormatter(null, null, true, true));
        Di::getDefault()->get(Logger::class)
            ->setHandlers([$this->testHandler]);
    }

    protected function getLogs(): array
    {
        return array_column($this->testHandler->getRecords(), 'formatted');
    }

    public function testStdInOut(): void
    {
        self::assertSame(12, fwrite(STDOUT, 'testStdInOut'));
        $logs = array_column($this->testHandler->getRecords(), 'formatted');
        self::assertEmpty($logs);
    }

    public function testFileWrite(): void
    {
        $fileHandler = fopen(__DIR__ . '/test.txt', 'w+b');
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
        self::assertTrue($this->testHandler->hasDebugThatContains('file_get_contents'));
    }

    public function testFilePutContents(): void
    {
        self::assertSame(4, file_put_contents(__DIR__ . '/test.txt', 'test'));
        $logs = $this->testHandler->getRecords();
        self::assertTrue($this->testHandler->hasDebugThatContains('file_put_contents'));
        self::assertStringContainsString('test.txt', $logs[0]['formatted']);
        self::assertStringContainsString('test', $logs[0]['formatted']);
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

        $logs = $this->testHandler->getRecords();
        self::assertStringContainsString('mysqli_connect', $logs[0]['formatted']);
        self::assertStringContainsString('SELECT 123', $logs[1]['formatted']);
    }

    public function testPDO(): void
    {
        $config = Di::get('config')['mysql'];
        $dbh = new PDO('mysql:dbname=;host=' . $config['host'], $config['username'], $config['password']);
        self::assertSame('123', $dbh->query("SELECT 123")->fetchColumn());

        $stmt = $dbh->prepare('SELECT :test;');
        $stmt->bindValue(':test', 123, PDO::PARAM_INT);
        $stmt->execute();
        self::assertSame('123', $stmt->fetchColumn());

        $logs = array_column($this->testHandler->getRecords(), 'formatted');
        self::assertStringContainsString('SELECT 123', $logs[0]);
        self::assertStringContainsString('SELECT 123', $logs[1]);
    }

    public function testCurl(): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"https://www.example.com/");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "postvar1=value1&postvar2=value2&postvar3=value3");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close ($ch);

        self::assertStringContainsString('html', $server_output);
        self::assertTrue($this->testHandler->hasDebugThatContains('curl -vX POST'));
    }
}