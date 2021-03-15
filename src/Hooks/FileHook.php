<?php

namespace Sanprojects\Interceptor\Hooks;

use Sanprojects\Interceptor\Logger;

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

    public static function stream_socket_client($remote_socket, &$errno = null, &$errstr = null, $timeout = null, $flags = null, $context = null)
    {
        $result = \stream_socket_client(...func_get_args());
        Logger::debug("stream_socket_client($remote_socket)=$result");

        return $result;
    }

    public static function socket_create($domain, $type, $protocol)
    {
        $result = \socket_create(...func_get_args());
        Logger::debug("socket_create($domain, $type, $protocol)=$result");

        return $result;
    }

    public static function fopen ($filename, $mode, $use_include_path = false, $context = null)
    {
        $result = \fopen(...func_get_args());
        Logger::debug("fopen($filename, $mode)=$result");

        return $result;
    }

    public static function fwrite($handle, $string)
    {
        $result = \fwrite(...func_get_args());
        Logger::debug("fwrite($handle, $string)=$result");

        return $result;
    }

    public static function fread($handle, $length = null)
    {
        $result = \fread(...func_get_args());
        Logger::debug("fread($handle, $length)=$result");

        return $result;
    }

    public static function fgets($handle, $length = null)
    {
        $result = \fgets(...func_get_args());

        Logger::debug("fgets($handle, $length)=$result");

        return $result;
    }

    public static function file_get_contents($filename, $use_include_path = false, $context = null, $offset = 0, $maxlen = null)
    {
        $result = file_get_contents(...func_get_args());

        Logger::debug("file_get_contents($filename)=" . $result);

        return $result;
    }

    public static function file_put_contents($filename, $data, $flags = 0, $context = null)
    {
        $result = file_put_contents(...func_get_args());

        Logger::debug("file_put_contents($filename, $data)=$result");

        return $result;
    }
}
