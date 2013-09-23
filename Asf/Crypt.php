<?php

/**
 * @author yuanjian
 *
 */
class Asf_Crypt {
    public static $instancePoll = array();

    public static function getInstance($name) {
        if (!$name) {
            throw new Asf_Crypt_Exception("can't getInstance without name");
            return ;
        }

        if (isset(self::$instancePoll[$name])) {
            return self::$instancePoll[$name];
        }

        $type = ucfirst(strtolower($name));

        //check the class file exist?
        if(!file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.
                "Crypt".DIRECTORY_SEPARATOR.$type.".php")) {
                throw new Asf_Crypt_Exception("Can't support handler $name now~");
                return;
        }

        $class = "Asf_Crypt_".$type;

        $instance = new $class();

        self::$instancePoll[$name] = $instance;

        return self::transToObj($instance);
        }

        private static function transToObj(Asf_Crypt_Interface $object) {
            return $object;
        }
}
