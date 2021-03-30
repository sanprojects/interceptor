<?php declare(strict_types=1);

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Sanprojects\Interceptor\Di;

final class InterceptorTest extends TestCase
{
    private TestHandler $testHandler;

    protected function setUp(): void
    {
        $this->testHandler = new TestHandler();
        $this->testHandler->clear();
        Di::getDefault()->get(Logger::class)->setHandlers([$this->testHandler]);
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
        self::assertStringContainsString('test.txt', $logs[0]['context'][0]);
        self::assertStringContainsString('test', $logs[0]['context'][1]);
    }
}