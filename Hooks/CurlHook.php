<?php

namespace Sanprojects\Interceptor\Hooks;

use Sanprojects\Interceptor\Logger;

class CurlHook extends Hook
{
    /**
     * @var string current status of this hook, either enabled or disabled
     */
    protected static $curlOpts = [];

    protected const HOOKED_FUNCTIONS = [
        'curl_exec',
        'curl_setopt',
        'curl_setopt_array',
        'curl_multi_exec',
    ];

    public static function curl_setopt_array($ch, $options)
    {
        $chNumber = (int) $ch;
        self::$curlOpts[$chNumber] = self::$curlOpts[$chNumber] ?? [];
        self::$curlOpts[$chNumber] = $options + self::$curlOpts[$chNumber];

        return \curl_setopt_array($ch, $options);
    }

    public static function curl_setopt($ch, $option, $value)
    {
        $chNumber = (int) $ch;
        self::$curlOpts[$chNumber] = self::$curlOpts[$chNumber] ?? [];
        self::$curlOpts[$chNumber][$option] = $value;

        return \curl_setopt($ch, $option, $value);
    }

    public static function curl_exec($ch)
    {
        $options = self::$curlOpts[(int) $ch] ?? [];
        self::$curlOpts[(int) $ch] = [];

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
            $versions = [
                CURL_HTTP_VERSION_NONE => '',
                CURL_HTTP_VERSION_1_0 => '--http1.0',
                CURL_HTTP_VERSION_1_1 => '--http1.1',
                CURL_HTTP_VERSION_2_0 => '--http2',
            ];
            $result[] = ' ' . $versions[$options[CURLOPT_HTTP_VERSION]] . ' \\';
        }

        if (isset($options[CURLOPT_UPLOAD])) {
            $result[] = " --upload '" . $options[CURLOPT_UPLOAD] . "' ";
        }

        $data = $options[CURLOPT_POSTFIELDS] ?? '';

        if (!empty($options[CURLOPT_INFILE]) && is_resource($options[CURLOPT_INFILE])) {
            fseek($options[CURLOPT_INFILE], 0);
            $data = fread($options[CURLOPT_INFILE], 999999) ?: $data;
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

        if (!empty($options[CURLOPT_READFUNCTION])) {
            $func = $options[CURLOPT_READFUNCTION];
            curl_setopt($ch, CURLOPT_READFUNCTION, function ($ch, $fh, $length) use (&$func, &$data, &$result) {
                $ret = $func($ch, $fh, $length);
                $result[] = 'CURL> ' . $ret;

                return $ret;
            });
        }

        $isWriteFunction = !isset($options[CURLE_ABORTED_BY_CALLBACK])
            && isset($options[CURLOPT_WRITEFUNCTION]);

        if ($isWriteFunction) {
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function (&$ch, &$str, &$result) use ($options) {
                $result[] = $str;
                if (isset($options[CURLOPT_WRITEFUNCTION])) {
                    return $options[CURLOPT_WRITEFUNCTION]($ch, $str);
                }

                return strlen($str);
            });
        }

        Logger::debug(implode("\n", $result));
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $content = \curl_exec($ch);

        $result = [];
        if (!$isWriteFunction) {
            if (!empty($options[CURLOPT_FILE]) && is_resource($options[CURLOPT_FILE])) {
                $pos = ftell($options[CURLOPT_FILE]);
                fseek($options[CURLOPT_FILE], 0); // silent crash app 502 Bad Gateway
                $result[] = fread($options[CURLOPT_FILE], 999999);
                fseek($options[CURLOPT_FILE], $pos);
            } else {
                $result[] = $content;
            }
        }

        Logger::debug(implode("\n", $result));//

        return $content;
    }

    public static function curl_multi_exec($mh, &$still_running)
    {
        Logger::debug('curl_multi_exec');

        return \curl_multi_exec($mh, $still_running);
    }
}
