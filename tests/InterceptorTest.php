<?php declare(strict_types=1);

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Sanprojects\Interceptor\Di;
use function PHPUnit\Framework\assertSame;

final class InterceptorTest extends TestCase
{
    public const CONFIG = [
        'mysql' => [
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
        ],
    ];

    private TestHandler $testHandler;

    protected function setUp(): void
    {
        $this->testHandler = new TestHandler();
        Di::getDefault()->get(Logger::class)->setHandlers([$this->testHandler]);
    }

    public function testRedis(): void
    {
        if (!class_exists('Redis')) {
            self::markTestSkipped('Redis not exists');
        }

        $redis = new Redis();
        $redis->pconnect('127.0.0.1', 6379);
        $redis->set('test', '123');

        $logs = $this->testHandler->getRecords();
        self::assertStringContainsString('123', $logs[0]['formatted']);
    }

    public function testMysqli(): void
    {
        $config = self::CONFIG['mysql'];
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
        $config = self::CONFIG['mysql'];
        $dbh = new PDO('mysql:dbname=;host=' . $config['host'], $config['username'], $config['password']);
        $stmt = $dbh->query('SELECT 123;');
        self::assertSame('123', $stmt->fetchColumn());

        $logs = $this->testHandler->getRecords();
        self::assertStringContainsString('SELECT 123', $logs[0]['formatted']);
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
}