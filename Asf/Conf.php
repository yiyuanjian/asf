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
                //throw new Exception("Need use default config, but not set");
                exit("Config Error: Need use default config, but not set. please set '_host','_default'");
            }
        }

        //all configs based on host should be a array. if assigned value is not
        // array, it should be a link.
        if(!is_array($confArray['_host'][$host])) {
            $hostStack = array();
            do {
                $host = $confArray['_host'][$host];
                if(!isset($confArray['_host'][$host])) {
                    exit("Can't find host $host in config file.");
                }
                if(in_array($host, $hostStack)) {
                    exit("Error: $host seems into infinate loop.");
                }
                array_push($hostStack, $host);
            } while(!is_array($confArray['_host'][$host]));
        }

        $confArray = array_merge($confArray, $confArray['_host'][$host]);
        self::$locked = 1;
        unset($confArray['_host']);

        //save to confs
        self::$confs = $confArray;
    }
}
