<?php

final class Asf_Rdb {
    private static $connPoll;

    const ASSOC = 1;
    const NUM = 2;
    const BOTH = 3;

    public static function getInstance($connStr) {
        if (!$connStr) {
            throw new Asf_Rdb_Exception("can't getInstance without config");
            return ;
        }

        $id = md5($connStr);
        if (isset(self::$connPoll[$id])) {
            return self::$connPoll[$id];
        }

        $configInfo = self::parseConnStr($connStr);
        if($configInfo == false) {
            throw new Asf_Rdb_Exception("can't parse config $connStr");
            return;
        }

        $dbType = ucfirst(strtolower($configInfo['type']));

        //check the class file exist?
        if(!file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.
                "Rdb".DIRECTORY_SEPARATOR.$dbType.".php")) {
            throw new Asf_Rdb_Exception("Can't support handler $dbType now~");
            return;
        }

        $class = "Asf_Rdb_".$dbType;

        $instance = new $class($configInfo);

        self::$connPoll[$id] = $instance;

        return self::transToObj($instance);
    }

    private static function transToObj(Asf_Rdb_Interface $object) {
        return $object;
    }

    public static function parseConnStr($str) {
        if (empty($str)) {
            return false;
        }

        $config = array("host" => "", "port" => 0, "charset" => "",
                    "user" => "", "password" => "", "dbname" => "");


        $match = null;

        if (preg_match('/([A-Za-z]+):\\/\\/([A-Za-z0-9_]*):([\x21-\x7e]*)@([a-z0-9.]+)(:[0-9]{3,5})?\\/([A-Za-z0-9\-_]+)(:[a-z0-9A-Z]{3,10})?/',$str, $match)) {
            $config['user'] = $match[2];
            $config['password'] = $match[3];
            $config['host'] = $match[4];
            $config['dbname'] = $match[6];
            if (isset($match[7])) {
                $config['charset'] = substr($match[7],1);
            }
            if (isset($match[5])) {
                $config['port'] = substr($match[5], 1);
            }
            $config['type'] = $match[1];

            return $config;
        }

        return false;
    }
}
