<?php

namespace Sanprojects\Interceptor\Hooks;

class CurlHook extends Hook
{
    protected const HOOKED_FUNCTIONS = [
        'curl_exec',
        'curl_setopt',
        'curl_setopt_array',
        'curl_multi_exec',
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

        return implode(" \\ \n", $result);
    }

    public static function curl_exec($ch)
    {
        $options = self::$curlOpts[(int) $ch] ?? [];
        self::$curlOpts[(int) $ch] = [];
        self::log(self::curlOptionsToCommand($options));

        $logFile = fopen('php://memory', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $logFile);
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $content = call_user_func_array(__FUNCTION__, func_get_args());
        fseek($logFile, 0);
        self::log('CURL> ' . stream_get_contents($logFile));

        return $content;
    }
}
