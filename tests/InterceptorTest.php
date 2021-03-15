<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Sanprojects\Interceptor\Interceptor;

final class InterceptorTest extends TestCase
{
    public function testIntercept(): void
    {
        self::assertSame('test', file_get_contents(__DIR__ . '/testFile.txt'));
    }
}