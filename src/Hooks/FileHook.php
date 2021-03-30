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
}
