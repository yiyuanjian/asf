<?php
class Asf_Handon {
    const HANDON_TERMINATE = 1;
    const HANDON_NEXT = 2;

    private $funs = array();

    public function __construct($dir = '') {
        if($dir) {
            $this->add($dir);
        }

        return $this;
    }

    public function __destruct() {
        $this->finish();
    }

    public function register($function) {
        //function
        if(is_string($function)) {
            if(preg_match('/^H(\d+)_([a-zA-Z0-9]+)$/', $function, $m)) {
                $priority = $m[1];
                if(isset($this->funs[$priority])) {
                    return false;
                }
                $this->funs[$priority] = $function;
                ksort($this->funs, SORT_NUMERIC);
                return true;
            }

            return false;
        }
        // class
        if(is_array($function)) {
            if(is_object($function[0])) {
                throw new Asf_Exception("You should not add a objected class for handon");
            }
            $className = $function[0];
            if(preg_match('/^H(\d+)_([a-zA-Z0-9]+)$/', $className, $m)) {
                $priority = intval($m[1]);
                if(isset($this->funs[$priority])) {
                    return false;
                }

                $obj = new $className();
                if(!($obj instanceof Asf_Handon_Interface)) {
                    unset($obj);
                    throw new Asf_Exception("class $className is not implement Asf_Handon_Interface");
                }
                $obj->setup();
                $function[0] = $obj;
                $this->funs[$priority] = $function;
                ksort($this->funs, SORT_NUMERIC);
                return true;
            }
            return false;
        }
    }

    public function add($dir) {
        if(!is_dir($dir)) {
            return false;
        }

        $rp = opendir($dir);
        if(!$rp) {
            return false;
        }
        while($file = readdir($rp)) {
            if($file == '.' || $file == '..') continue;
            include $dir.DIRECTORY_SEPARATOR.$file;

            //TODO:auto register
            $name = substr($file,0,strrpos($file, '.'));
            if(class_exists($name)) {
                //echo "register as class:";
                $this->register(array($name, 'run'));
            }
            if(function_exists($name)) {
                //echo "register as function: ";
                $this->register($name);
            }
        }
        closedir($rp);

        return true;
    }

    public function run() {
        if(empty($this->funs)) {
            return true;
        }

        $args = func_get_args();

        foreach($this->funs as $p => $f) {
            $ret = call_user_func_array($f, $args);
            if($ret === self::HANDON_TERMINATE) break;
        }

        return true;
    }

    public function getAllFuns() {
        ksort($this->funs);

        return array_values($this->funs);
    }

    public function unregister($prority) {
        if(isset($this->funs[$prority])) {
            if(is_array($this->funs[$prority])) {
                $this->funs[$prority][0]->teardown();
            }
            unset($this->funs[$prority]);

            return true;
        }

        return false;
    }

    public function finish() {
        if(empty($this->funs)) return true;

        foreach ($this->funs as $p => $f) {
            if(is_array($f)) {
                $f[0]->teardown();
            }
        }
        //clean registered function
        $this->funs = array();

        return true;
    }
}