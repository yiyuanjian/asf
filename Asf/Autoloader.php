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

    private static function autoload($class) {
        $path = "";
        $match = null;
        if(preg_match('/[A-Za-z_]+(Controller|Model|View|Script)$/', $class, $match)) {
            $path = APP_ROOT.'/'.strtolower($match[1]).'s/';
        }

        $class = str_replace('_', '/', $class);

        require_once $path.$class.".php";
    }
}
