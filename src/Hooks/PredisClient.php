<?php

namespace Sanprojects\Interceptor\Hooks;

class PredisClient extends \Predis\Client
{
    private string $connectionHost;

    public function __construct($parameters = null, $options = null)
    {
        $this->connectionHost = $this->getHost($parameters);

        Hook::hookFunction(
            fn() => parent::__construct(...func_get_args()),
            func_get_args(),
            [],
            'Redis::__construct'
        );
    }

    private function getHost($parameters)
    {
        $params = $parameters ?? 'tcp://127.0.0.1:6379';
        $params = is_array($params) ? $params : parse_url($params);

        return ($params['scheme'] ?? '') . '://' . ($params['host'] ?? '') . ':' . ($params['port'] ?? '');
    }

    public function __call($commandID, $arguments)
    {
        return Hook::hookFunction(
            fn() => parent::__call(...func_get_args()),
            func_get_args(),
            $arguments,
            'Redis ' . $this->connectionHost . ' ' . $commandID
        );
    }
}
