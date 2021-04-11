<?php

namespace Sanprojects\Interceptor\Hooks;

class PredisClient extends \Predis\Client
{
    private string $connectionString;

    public function __construct($parameters = null, $options = null)
    {
        $params = $parameters ?? 'tcp://127.0.0.1:6379';
        $this->connectionString = is_string($params)
            ? $params
            : json_encode($params);

        Hook::hookFunction(
            fn() => parent::__construct(...func_get_args()),
            func_get_args(),
            [$this->connectionString],
            'Redis::__construct'
        );
    }

    public function __call($commandID, $arguments)
    {
        return Hook::hookFunction(
            fn() => parent::__call(...func_get_args()),
            func_get_args(),
            $arguments,
            'Redis ' . $this->connectionString . ' ' .$commandID
        );
    }
}
