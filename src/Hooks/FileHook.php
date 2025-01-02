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

    protected const EXCLUDED_FILENAMES = [
        'php://stdout',
        'php://stderr',
        'php://temp',
        'php://input',
        'php://memory',
    ];
    protected static $fileHandlers = [];

    public static function stream_socket_client($remote_socket, &$errno, &$errstr, $timeout = null, $flags = null, $context = null)
    {
        $result = self::hookFunction(__FUNCTION__, [$remote_socket, &$errno, &$errstr, $timeout, $flags]);
        self::$fileHandlers[(int) $result] = $remote_socket;

        return $result;
    }

    public static function fopen($filename, $options)
    {
        if (self::isExcluded(null, $filename)) {
            $result = call_user_func_array(__FUNCTION__, func_get_args());
        } else {
            $result = self::hookFunction(__FUNCTION__, func_get_args());
        }

        self::$fileHandlers[(int) $result] = $filename;

        return $result;
    }

    public static function fwrite($handler)
    {
        if (self::isExcluded($handler)) {
            return call_user_func_array(__FUNCTION__, func_get_args());
        }

        $filename = self::$fileHandlers[(int) $handler] ?? '';
        $args = func_get_args();
        $args[0] = $filename;

        return self::hookFunction(__FUNCTION__, func_get_args(), $args);
    }

    public static function fread($handler)
    {
        if (self::isExcluded($handler)) {
            return call_user_func_array(__FUNCTION__, func_get_args());
        }

        $filename = self::$fileHandlers[(int) $handler] ?? '';
        $args = func_get_args();
        $args[0] = $filename;

        return self::hookFunction(__FUNCTION__, func_get_args(), $args);
    }

    public static function fgets($handler)
    {
        if (self::isExcluded($handler)) {
            return call_user_func_array(__FUNCTION__, func_get_args());
        }

        $filename = self::$fileHandlers[(int) $handler] ?? '';

        $args = func_get_args();
        $args[0] = $filename;

        return self::hookFunction(__FUNCTION__, func_get_args(), $args);
    }

    private static function isExcluded($handler, $fileName = '')
    {
        if (defined('STDIN')) {
            if ($handler && in_array($handler, [STDIN, STDERR, STDOUT], true)) {
                return true;
            }
        }

        $filename = $fileName ?: (self::$fileHandlers[(int) $handler] ?? '');

        return in_array($filename, self::EXCLUDED_FILENAMES, true);
    }
}
