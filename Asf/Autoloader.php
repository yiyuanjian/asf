<?php

class Asf_Autoloader {
    public static function regist() {
        //add APP_ROOT/app/library to include_path
        $appLibray = APP_ROOT."/library";
        $paths = explode(PATH_SEPARATOR,get_include_path());
        if(!in_array($appLibray, $paths)) {
            set_include_path(get_include_path().PATH_SEPARATOR.$appLibray);
        }

        spl_autoload_register(array(__CLASS__, "autoload"));

        return true;
    }

    public static function autoload($class) {
        $path = "";
        $match = null;
        if (!strncmp($class, "Asf_", 4)) {
            $path = ASF_ROOT.'/';
        } else {
            $rClass = strrev($class);
            if (strncmp($rClass, 'rellortnoC', 10) == 0) {
                $path = APP_ROOT.'/controllers/';
            } elseif(strncmp($rClass, 'ledoM', 5) == 0) {
                $path = APP_ROOT.'/models/';
            } elseif(strncmp($rClass, 'weiV', 4) == 0) {
                $path = APP_ROOT.'/views/';
            } elseif(strncmp($rClass, 'tpircS', 6) == 0) {
                $path = APP_ROOT.'/scripts/';
            }
        }

        $class = str_replace('_', '/', $class);

        require_once $path.$class.".php";
    }
}
