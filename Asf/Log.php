<?php

/**
 * @author yuanjian
 *
 */
class Asf_Log {
    const LOG_EMERG = 0;
    const LOG_ALERT = 1;
    const LOG_CRITICAL = 2;
    const LOG_ERROR = 3;
    const LOG_WARNING = 4;
    const LOG_NOTICE = 5;
    const LOG_INFO = 6;
    const LOG_DEBUG = 7;

    const LOG_MESSAGE = 8; //log message directly

    const LOG_EXCEPTION = 9;

    private static $instancePoll;

    public static function getInstance($conf) {
        if (empty($conf)) {
            throw new Asf_Log_Exception("conf is empty.",
                        Asf_Log_Exception::NOT_ASSIGN_ANY_HANDLER);
            return null;
        }
        try {
            $confs = self::parseConf($conf);
        } catch (Asf_Log_Exception $e) {
            throw $e;
        }

        $id = md5($conf);
        if(isset(self::$instancePoll[$id])) {
            return self::$instancePoll[$id];
        }

        $handler = ucfirst(strtolower($confs['handler']));

        //check the class file exist?
        if(!file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.
                "Log".DIRECTORY_SEPARATOR.$handler.".php")) {
                throw new Asf_Log_Exception("Can't support handler $handler now~");
                return;
        }

        $class = "Asf_Log_".$handler;

        $instance = new $class($confs['category'], $confs['path'],
                                $confs['splitByDay']);

        return self::transToObj($instance);
    }

    private static function parseConf($conf) {
        $match = array();
        if(!preg_match('/^([a-z]+):\/\/(.+)\/([a-zA-Z0-9\-_\.]+):?([01]?)$/',$conf, $match)) {
            throw new Asf_Log_Exception("conf parse failed",
                        Asf_Log_Exception::PARSE_CONF_FAILED);
            return false;
        }


        $cfg['handler'] = ucwords($match[1]);
        $cfg['path'] = $match[2];
        $cfg['category'] = $match[3];
        $cfg['splitByDay'] = isset($match[4]) && $match[4] ? true: false;

        return $cfg;
    }

    private static function transToObj(Asf_Log_Interface $object) {
        return $object;
    }
}
