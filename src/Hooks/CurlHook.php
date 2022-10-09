<?php

namespace Sanprojects\Interceptor\Hooks;

class CurlHook extends Hook
{
    protected const HOOKED_FUNCTIONS = [
        'curl_exec',
        'curl_multi_exec',
        'curl_setopt',
        'curl_setopt_array',
    ];

    protected const CURL_VERSIONS = [
        CURL_HTTP_VERSION_NONE => '',
        CURL_HTTP_VERSION_1_0 => '--http1.0',
        CURL_HTTP_VERSION_1_1 => '--http1.1',
        CURL_HTTP_VERSION_2_0 => '--http2',
    ];

    /**
     * @var string current status of this hook, either enabled or disabled
     */
    protected static $curlOpts = [];

    public static function curl_setopt_array($ch, $options)
    {
        $chNumber = (int) $ch;
        self::$curlOpts[$chNumber] = self::$curlOpts[$chNumber] ?? [];
        self::$curlOpts[$chNumber] = $options + self::$curlOpts[$chNumber];

        return call_user_func_array(__FUNCTION__, func_get_args());
    }

    public static function curl_setopt($ch, $option, $value)
    {
        $chNumber = (int) $ch;
        self::$curlOpts[$chNumber] = self::$curlOpts[$chNumber] ?? [];
        self::$curlOpts[$chNumber][$option] = $value;

        return call_user_func_array(__FUNCTION__, func_get_args());
    }

    public static function curlOptionsToCommand($options)
    {
        $result = [];

        $isPostFields = strpos($options[CURLOPT_POSTFIELDS] ?? '', '%2B')
            || strpos($options[CURLOPT_POSTFIELDS] ?? '', '%22')
            || strpos($options[CURLOPT_POSTFIELDS] ?? '', '%5B');

        $method = ($options[CURLOPT_CUSTOMREQUEST] ?? '') ?: (isset($options[CURLOPT_POST]) ? 'POST' : 'GET');
        $url = $options[CURLOPT_URL] ?? '';
        $result[] = "curl -vX $method '$url'";

        foreach ($options[CURLOPT_HTTPHEADER] ?? [] as $k => $v) {
            if (!$v || $v[strlen($v) - 1] === ':') {
                continue;
            }

            if (strpos($v, 'Content-Type:') === 0 && $isPostFields) {
                $v = 'Disabled-' . $v;
            }
            $result[] = " -H '" . $v . "'";
        }

        if (isset($options[CURLOPT_TIMEOUT])) {
            $result[] = " --max-time '" . $options[CURLOPT_TIMEOUT] . "'";
        }
        if (isset($options[CURLOPT_CONNECTTIMEOUT])) {
            $result[] = " --connect-timeout '" . $options[CURLOPT_CONNECTTIMEOUT] . "'";
        }
        if (isset($options[CURLOPT_CONNECTTIMEOUT_MS])) {
            $result[] = " --connect-timeout  '" . round($options[CURLOPT_CONNECTTIMEOUT_MS] / 1000, 3) . "'";
        }
        if (isset($options[CURLOPT_HTTP_VERSION])) {
            $result[] = ' ' . (self::CURL_VERSIONS[$options[CURLOPT_HTTP_VERSION]] ?? '');
        }
        if (isset($options[CURLOPT_UPLOAD])) {
            $result[] = " --upload '" . $options[CURLOPT_UPLOAD] . "' ";
        }

        $data = $options[CURLOPT_POSTFIELDS] ?? '';

        if (!empty($options[CURLOPT_INFILE]) && is_resource($options[CURLOPT_INFILE])) {
            fseek($options[CURLOPT_INFILE], 0);
            $data = fgets($options[CURLOPT_INFILE]) ?: $data;
            fseek($options[CURLOPT_INFILE], 0);
        }

        if ($isPostFields) {
            $fields = explode('&', $options[CURLOPT_POSTFIELDS]);
            foreach ($fields as $index => $field) {
                $result[] = " -F '" . urldecode($field) . "' ";
            }
        } elseif ($data) {
            $result[] = " --data '$data'";
        }

        return implode(" \\\n", $result);
    }

    public static function curl_exec($ch)
    {
        $options = self::$curlOpts[(int) $ch] ?? [];
        self::$curlOpts[(int) $ch] = [];
        self::log(self::curlOptionsToCommand($options));


        if (!empty($options[CURLOPT_INFILE]) && is_resource($options[CURLOPT_INFILE])) {
            fseek($options[CURLOPT_INFILE], 0);
            error_log('CURL> ' . fgets($options[CURLOPT_INFILE]));
            fseek($options[CURLOPT_INFILE], 0);
        }

        if (!empty($options[CURLOPT_READFUNCTION])) {
            $func = $options[CURLOPT_READFUNCTION];
            \curl_setopt($ch, CURLOPT_READFUNCTION, function ($ch, $fh, $length) use (&$func, &$data) {
                $ret = $func($ch, $fh, $length);
                error_log('CURL> ' . $ret);

                return $ret;
            });
        }

        $isWriteFunction = !isset($options[CURLE_ABORTED_BY_CALLBACK]) && isset($options[CURLOPT_WRITEFUNCTION]);

        if ($isWriteFunction) {
            \curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $str) use ($options) {
                self::log('curl write> ' . $str);

                if (isset($options[CURLOPT_WRITEFUNCTION])) {
                    return $options[CURLOPT_WRITEFUNCTION]($ch, $str);
                }

                return strlen($str);
            });
        }

        $logFile = fopen('php://memory', 'w+');
        \curl_setopt($ch, CURLOPT_STDERR, $logFile);
        \curl_setopt($ch, CURLOPT_VERBOSE, true);

        $content = \curl_exec(...func_get_args());
        fseek($logFile, 0);
        self::log('curl log> ' . stream_get_contents($logFile));

        if (!$isWriteFunction) {
            $file = $options[CURLOPT_FILE] ?? null;
            if (is_resource($file)) {
                $pos = ftell($file);
                fseek($file, 0);
                self::log('curl file> ' . fgets($file));
                fseek($file, $pos);
            } else {
                self::log('curl result> ' . $content);
            }
        }

        return $content;
    }

    public static function curl_multi_exec($multi_handle, &$still_running = 0)
    {
        $status = curl_multi_exec($multi_handle, $still_running);

        if (!$still_running) {
            foreach (self::$curlOpts as $ch => $options) {
                self::log(self::curlOptionsToCommand($options));
            }
        }

        return $status;
    }
}
