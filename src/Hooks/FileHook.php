<?php

namespace Sanprojects\Interceptor\Hooks;

class FileHook extends Hook
{
    protected const HOOKED_FUNCTIONS = [
        'socket_create',
        'stream_socket_client',
        'fopen',
        'fwrite',
        'fread',
        'fgets',
        'file_get_contents',
        'file_put_contents',
    ];

    protected static $fileHandlers = [];

    public static function fopen($filename, $options)
    {
        $result = call_user_func_array(__FUNCTION__, func_get_args());
        self::$fileHandlers[(int) $result] = $filename;

        return $result;
    }

    public static function fwrite($handler)
    {
        // skip std in/out
        if ($handler === STDOUT) {
            return call_user_func_array(__FUNCTION__, func_get_args());
        }

        $filename = self::$fileHandlers[(int) $handler] ?? '';

        return self::hookFunction(__FUNCTION__, func_get_args(), ['filename' => $filename]);
    }

    public static function fread($handler)
    {
        // skip std in/out
        if ($handler === STDIN) {
            return call_user_func_array(__FUNCTION__, func_get_args());
        }

        $filename = self::$fileHandlers[(int) $handler] ?? '';

        return self::hookFunction(__FUNCTION__, func_get_args(), ['filename' => $filename]);
    }
}
