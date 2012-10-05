<?php
/**
 * @author yuanjian
 */

class Asf_Kvdb {
    private static $connPoll;

    public static function getInstance($connStr) {
        if (!$connStr) {
            throw new Asf_Kvdb_Exception("can't getInstance without config");
            return ;
        }

        $id = md5($connStr);
        if (isset(self::$connPoll[$id])) {
            return self::$connPoll[$id];
        }

        $configInfo = self::parseConnStr($connStr);
        if($configInfo == false) {
            throw new Asf_Kvdb_Exception("can't parse config $connStr");
            return;
        }

        $dbType = ucfirst(strtolower($configInfo['type']));

        //check the class file exist?
        if(!file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.
                "Kvdb".DIRECTORY_SEPARATOR.$dbType.".php")) {
            throw new Asf_Kvdb_Exception("Can't support handler $dbType now~");
            return;
        }
        $class = "Asf_Kvdb_".$dbType;

        $instance = new $class($configInfo);

        self::$connPoll[$id] = $instance;

        return self::transToObj($instance);
    }

    private static function transToObj(Asf_Kvdb_Interface $object) {
        return $object;
    }

    public static function parseConnStr($str) {
        if (empty($str)) {
            return false;
        }

        $config = array("host" => "", "port" => 0);

        $match = null;

        if (preg_match('/([A-Za-z]+):\\/\\/([a-z0-9.]+)(:[0-9]{3,5})?/',$str, $match)) {
            $config['host'] = $match[2];
            if (isset($match[3])) {
                $config['port'] = substr($match[3], 1);
            }
            $config['type'] = $match[1];

            return $config;
        }

        return false;
    }
}
