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
        $result = \stream_socket_client($remote_socket, $errno, $errstr, $timeout, $flags);
        Logger::debug("stream_socket_client($remote_socket)=$result");

        return $result;
    }

    public static function socket_create($domain, $type, $protocol)
    {
        $result = \socket_create($domain, $type, $protocol);
        Logger::debug("socket_create($domain, $type, $protocol)=$result");

        return $result;
    }

    public static function fopen ($filename, $mode, $use_include_path = false, $context = null)
    {
        $result = \fopen($filename, $mode, $use_include_path, $context);
        Logger::debug("fopen($filename, $mode)=$result");

        return $result;
    }

    public static function fwrite($handle, $string)
    {
        $result = \fwrite($handle, $string);
        Logger::debug("fwrite($handle, $string)=$result");

        return $result;
    }

    public static function fread($handle, $length = null)
    {
        $result = \fread($handle, $length);
        Logger::debug("fread($handle, $length)=$result");

        return $result;
    }

    public static function fgets($handle, $length = null)
    {
        if ($length === null) {
            $result = \fgets($handle);
        } else {
            $result = \fgets($handle, $length);
        }

        Logger::debug("fgets($handle, $length)=$result");

        return $result;
    }

    public static function file_get_contents($filename, $use_include_path = false, $context = null, $offset = 0, $maxlen = null)
    {
        $result = file_get_contents($filename, $use_include_path, $context, $offset, $maxlen);

        Logger::debug("file_get_contents($filename)=" . $result);

        return $result;
    }

    public static function file_put_contents($filename, $data, $flags = 0, $context = null)
    {
        $result = file_put_contents($filename, $data, $flags = 0, $context = null);

        Logger::debug("file_put_contents($filename, $data)=$result");

        return $result;
    }
}
