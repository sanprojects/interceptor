<?php

namespace Sanprojects\Interceptor;

use Sanprojects\Interceptor\Hooks\AMQPHook;
use Sanprojects\Interceptor\Hooks\CurlHook;
use Sanprojects\Interceptor\Hooks\FileHook;
use Sanprojects\Interceptor\Hooks\MysqliHook;
use Sanprojects\Interceptor\Hooks\PDOHook;
use Sanprojects\Interceptor\Hooks\RedisHook;
use Sanprojects\Interceptor\Hooks\RdKafka\RdKafkaHook;
use Sanprojects\Interceptor\Logger\Logger;

class Interceptor
{
    /**
     * @var callable[] transformers which have been appended to this stream processor
     */
    protected static array $hooks = [];

    public function __construct() {}

    public function intercept(): void
    {
        $codeTransformer = static function (string $code) {
            foreach (static::$hooks as $codeTransformer) {
                $code = $codeTransformer($code);
            }

            return $code;
        };

        $streamWrapper = new StreamWrapper();
        $streamWrapper->setFilterCallBack($codeTransformer);
        $streamWrapper->registerWrapper();
    }

    /**
     * Adds code transformer to the stream processor.
     */
    public function addHook(callable $hook): self
    {
        static::$hooks[] = $hook;

        return $this;
    }

    public function addAllHooks(): self
    {
        return $this
            ->addHook([new CurlHook(), 'filter'])
            ->addHook([new FileHook(), 'filter'])
            ->addHook([new AMQPHook(), 'filter'])
            ->addHook([new RedisHook(), 'filter'])
            ->addHook([new MysqliHook(), 'filter'])
            ->addHook([new PDOHook(), 'filter'])
            ->addHook([new RdKafkaHook(), 'filter']);
    }

    public static function interceptAll(): self
    {
        $interceptor = new self();
        $interceptor
            ->addAllHooks()
            ->intercept();

        Di::get(Logger::class)->debug('Code wrapper registered');

        return $interceptor;
    }

    public static function load(): void
    {
        if (
            (isset($_REQUEST['interceptor']) && $_REQUEST['interceptor'])
            || (isset($_ENV['interceptor']) && $_ENV['interceptor'])
            || in_array('--interceptor', $_SERVER['argv'] ?? [], true)
        ) {
            self::interceptAll();
        }
    }
}
