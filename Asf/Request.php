<?php

/**
 * @author yuanjian
 *
 */
class Asf_Request {
    private static $dataFrom = 'r';

    public static function setDataFromPost() {
        self::$dataFrom = 'p';
    }

    public static function setDataFromGet() {
        self::$dataFrom = 'g';
    }

    public static function setDataFromRequest() {
        self::$dataFrom = 'r';
    }

    public static function setDataFromServer() {
        self::$dataFrom = 's';
    }

    public static function setDataFromEnv() {
        self::$dataFrom = 'e';
    }

    public static function setDataFromCookie() {
        self::$dataFrom = 'c';
    }

    private static function checkFrom() {
        switch (self::$dataFrom) {
            case 'r':
                $ret = &$_REQUEST;
                break;
            case 'g':
                $ret = &$_GET;
                break;
            case 'p':
                $ret = &$_POST;
                break;
            case 'e':
            case 's':
                $ret = &$_SERVER;
                break;
            case 'c':
                $ret = &$_COOKIE;
                break;
            default:
                $ret = &$_REQUEST;
            break;
        }

        return $ret;
    }


    public static function getInt($param) {
        $ret = self::checkFrom();
        return isset($ret[$param]) ? intval($ret[$param]) : 0;
    }

    private static function processString($string, $maxlen, $slash, $charset = 'utf-8') {
        if(!get_magic_quotes_gpc() && $slash) {
            $string = addslashes($string);
        }

        if($maxlen) {
            return function_exists("mb_substr")
                ? mb_substr($string, 0, $maxlen, $charset)
                : Asf_String::subStrByByte($string, $maxlen, 0, $charset);
        }


        return $string;
    }

    public static function getString($param, $maxlen = 0, $slash = true, $charset = "utf-8") {
        $ret = self::checkFrom();
        return isset($ret[$param])
            ? self::processString($ret[$param], $maxlen, $slash, $charset)
            : "";
    }

    public static function getArray($param, $count = 0) {
        $ret = self::checkFrom();

        $array = (isset($ret[$param]) && is_array($ret[$param]))
                    ? $ret[$param] : array($ret[$param]);

        if($count) {
            return array_slice($array, 0, $count);
        }

        return $array;
    }

    public static function getDecimal($param, $m = 20, $n = 10) {
        $ret = self::checkFrom();
    }

    public static function getDate($param) {
        $ret = self::checkFrom();
        if(!isset($ret[$param])) {
            return false;
        }

        if(self::processDate($ret[$param])) {
            return $ret[$param];
        }

        return false;
    }

    public static function getTime($param) {
        $ret = self::checkFrom();
        if(!isset($ret[$param])) {
            return false;
        }

        if(self::processTime($ret[$param])) {
            return $ret[$param];
        }

        return false;
    }

    public static function getDateTime($param) {
        $ret = self::checkFrom();
        if(!isset($ret[$param])) {
            return false;
        }

        if(self::processDateTime($ret[$param])) {
            return $ret[$param];
        }

        return false;
    }

    public static function getCookieString($param, $maxlen = 0, $slash = true, $charset = "utf-8") {
        return isset($_COOKIE[$param])
            ? self::processString($_COOKIE[$param], $maxlen, $slash, $charset)
            : "";
    }

    public static function getCookieInt($param) {
        if(!isset($_COOKIE[$param])) {
            return null;
        }

        return intval($_COOKIE[$param]);
    }

    private static function processDate($date) {
        return preg_match('/^(19|20)[0-9]{2}-(0[1-9]|1[012]|[0-9])-([1-9]|0[1-9]|[12][0-9]|[3][01])$/', $date);
    }

    private static function processTime($time) {
        return preg_match('/^([01]?[0-9]|2[0-3]):([0-5]?[0-9]):([0-5]?[0-9])$/', $time);
    }

    private static function processDateTime($datetime) {
        return preg_match('/^(19|20)[0-9]{2}-(0[1-9]|1[012]|[0-9])-([1-9]|0[1-9]|[12][0-9]|[3][01])\s+([01]?[0-9]|2[0-3]):([0-5]?[0-9]):([0-5]?[0-9])$/', $datetime);
    }

    /**
     * This function will return HTTP_HOST in web app,
     * return SERVER_NAME in cli, before run cli, you need export to enviroment
     * variables.
     */
    public static function getHost() {
        if(php_sapi_name() == "cli") {
            return isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : gethostname();
        }

        return $_SERVER['HTTP_HOST'];
    }

    public static function getRealIp() {
        $headers = self::getAllHeader();
        if(isset($headers['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }

        return $_SERVER['REMOTE_ADDR'];
    }

    public static function getAllHeader() {
        if (function_exists("getallheaders")) {
            $headers = getallheaders();
            $newHeaders = array();
            foreach($headers as $k => $v) {
                $k = str_replace("-", "_", strtoupper($k));
                if(substr($k, 0, 5) != "HTTP_") {
                    $newHeaders['HTTP_'.$k] = $v;
                }
                $newHeaders[$k] = $v;
            }

            return $newHeaders;
        }

        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if ('HTTP_' == substr($key, 0, 5)) {
                $headers[substr($key, 5)] = $value;
                $headers[$key] = $value;
            }
        }
        if (isset($_SERVER['PHP_AUTH_DIGEST'])) {
            $header['AUTHORIZATION'] = $_SERVER['PHP_AUTH_DIGEST'];
        } elseif (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            $header['AUTHORIZATION'] = base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
        }
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $header['CONTENT_LENGTH'] = $_SERVER['CONTENT_LENGTH'];
        }
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $header['CONTENT_TYPE'] = $_SERVER['CONTENT_TYPE'];
        }

        return $headers;
    }

    public static function getHeaderByName($param) {
        $headers = self::getAllHeader();

        return isset($headers[$param]) ? $headers[$param] : "";
    }

    public static function getRefer() {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "";
    }
}
