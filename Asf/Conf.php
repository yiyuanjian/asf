<?php

/**
 * @author yuanjian
 *
 */
class Asf_Conf {
    private static $confs;
    private static $locked = 0;

    public static function get($key) {
        if(!is_string($key) || empty($key)) {
            return null;
        }

        if(isset(self::$confs[$key])) {
            return self::$confs[$key];
        }

        return null;
    }

    public static function set($key, $value) {
        if(!is_string($key) || empty($key)) {
            return false;
        }

        self::$confs[$key] = $value;

        return true;
    }

    public static function init($confArray) {
        if(self::$locked) {
            exit("Config Error: Can't assign configs after init.");
        }
        if(!isset($confArray['_host'])){
            self::$confs = $confArray;
            return;
        }

        $host = Asf_Request::getHost();
        if(!isset($confArray['_host'][$host])) {
            if(!isset($confArray['_host']['_default'])) {
                exit("Config Error: Not assign default host set");
            }
            $host = $confArray['_host']['_default'];
            if(!isset($confArray['_host'][$host])) {
                //FIXME: throw exception or exit directly?
                //throw new Exception("Need use default config, but not set");
                exit("Config Error: Need use default config, but not set. please set '_host','_default'");
            }
        }

        $confArray = array_merge($confArray, $confArray['_host'][$host]);
        self::$locked = 1;
        //FIXME: unset($confArray['_host']); ?

        //save to confs
        self::$confs = $confArray;
    }
}
