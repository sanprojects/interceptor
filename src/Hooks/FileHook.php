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

    public static function stream_socket_client($remote_socket, &$errno, &$errstr, $timeout = null, $flags = null, $context = null)
    {
        $result = self::hookFunction(__FUNCTION__, [$remote_socket, &$errno, &$errstr, $timeout, $flags]);
        //$result = stream_socket_client($remote_socket, $errno, $errstr, $timeout, $flags);
        self::$fileHandlers[(int) $result] = $remote_socket;

        return $result;
    }

    public static function fopen($filename, $options)
    {
        $result = self::hookFunction(__FUNCTION__, func_get_args());
        //$result = call_user_func_array(__FUNCTION__, func_get_args());
        self::$fileHandlers[(int) $result] = $filename;

        return $result;
    }

    public static function fwrite($handler)
    {
        // skip std in/out
        if (in_array($handler, [STDOUT, STDERR])) {
            return call_user_func_array(__FUNCTION__, func_get_args());
        }

        $filename = self::$fileHandlers[(int) $handler] ?? '';

        if (in_array($filename, ['php://stderr'])) {
            return call_user_func_array(__FUNCTION__, func_get_args());
        }

        $args = func_get_args();
        $args[0] = $filename;
        return self::hookFunction(__FUNCTION__, func_get_args(), $args);
    }

    public static function fread($handler)
    {
        // skip std in/out
        if ($handler === STDIN) {
            return call_user_func_array(__FUNCTION__, func_get_args());
        }

        $filename = self::$fileHandlers[(int) $handler] ?? '';

        $args = func_get_args();
        $args[0] = $filename;
        return self::hookFunction(__FUNCTION__, func_get_args(), $args);
    }

    public static function fgets($handler)
    {
        // skip std in/out
        if ($handler === STDIN) {
            return call_user_func_array(__FUNCTION__, func_get_args());
        }

        $filename = self::$fileHandlers[(int) $handler] ?? '';

        $args = func_get_args();
        $args[0] = $filename;
        return self::hookFunction(__FUNCTION__, func_get_args(), $args);
    }
}
