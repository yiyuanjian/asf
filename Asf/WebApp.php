<?php
if(!defined("APP_ROOT")) {
    exit("You must define APP_ROOT first.");
}

class Asf_WebApp {
    public function __construct($conf = array()) {
        require 'Asf/Autoloader.php';
        Asf_Autoloader::regist();

        $conf && Asf_Conf::init($conf);


        //regist enviorment
        $environments = Asf_Conf::get("__environment");
        if(is_array($environments)) {
            foreach ($environments as $env => $value) {
                $_SERVER[$env] = $value;
            }
        }
    }

    public function bootstrap() {
        //run boost file
        if(file_exists(APP_ROOT."/bootstrap.php")) {
            //TODO: run boost all method by order
        }
        return $this;
    }

    public function run() {
        Asf_Request::setDataFromGet();
        $ctl = Asf_Request::getString("__c", 20);
        $act = Asf_Request::getString("__a", 40);
        Asf_Request::setDataFromRequest();

        try {
            $ctl = $ctl ? Asf_Format::str2Standard($ctl, true)."Controller" : "IndexController";
            $act = $act ? Asf_Format::str2Standard($act)."Action" : "indexAction";

            $classFile = APP_ROOT."/controllers/".$ctl.".php";
            if (!file_exists($classFile)) {
                header("HTTP/1.0 404 Not Found");
                exit();
            }

            //run action in controller
            $controller = new $ctl;

            //run Actions
            if(method_exists($controller, $act)) {
                $controller->$act();
            } else {
                $controller->indexAction();
            }

        } catch (Exception $e) {
            trigger_error($e->__toString(), E_USER_ERROR);
        }
    }
}
