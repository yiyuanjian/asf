<?php

class Asf_CliApp {

    public function __construct($conf = array(), $host = "") {
        require 'Asf/Autoloader.php';
        Asf_Autoloader::regist();

        if($host) {
            $_SERVER['SERVER_NAME'] = $host;
        }

        $conf && Asf_Conf::init($conf);

    }

    public function bootstrap() {
        //TODO:

        return $this;
    }

    public function run() {
        //process argument, add to $_GET or Asf_Global?

        //argc can not accessable here. user $_SERVER['argc']
        $argumentArray = array();
        if($_SERVER['argc'] > 1) {
            $args = &$_SERVER['argv'];
            for ($i = 1; $i < $_SERVER['argc']; $i++) {
                $arg = next($args);

                if($arg == false) {
                    break;
                }

                if($arg == "-c") {  //controller
                    $ctl = next($args);
                    if(substr($ctl, 0, 1) == '-') {
                        $ctl = NULL;
                        prev($args);
                    } else {
                        $i++;
                    }
                    continue;
                }

                if($arg == "-a") { //action
                    $act = next($args);
                    if(substr($act, 0, 1) == '-') {
                        $act = NULL;
                        prev($args);
                    } else {
                        $i++;
                    }
                    continue;
                }

                $output = array();
                parse_str($arg, $output);
                $argumentArray = array_merge_recursive($argumentArray, $output);
            }
        }

        foreach ($argumentArray as $k => $v) {
            Asf_Gdata::set($k, $v);
        }

        $ctl = $ctl ? Asf_Format::str2Standard($ctl, true)."Script" : "IndexScript";
        $act = $act ? Asf_Format::str2Standard($act)."Action" : "indexAction";

        try {
            $classFile = APP_ROOT."/scripts/".$ctl.".php";
            if (!file_exists($classFile)) {
                throw new Asf_Exception("Script File $classFile not exist!");
            }

            //run action in controller
            $controller = new $ctl;

            //run Actions
            if(method_exists($controller, $act)) {
                $controller->$act();
            } else {
                $controller->indexAction();
            }
        } catch (Asf_Exception $e) {
            echo $e->__toString();
            exit();
        } catch (Exception $e) {
            echo $e->__toString();
            exit();
        }
    }
}

