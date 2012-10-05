<?php
class Asf_Template {
    private $data = array();

    private $config = array("template_dir" => "templates",
        "cache_dir" => "../cache",
        "check_include" => true,
        "suffix" => ".html");

    private $includeStack = array();

    private $tpl = "";

    public function __construct($tpl = '') {
        if(is_string($tpl)) {
            $this->tpl = $tpl;
        }

    }

    public function setConfig($config = array()){
        if (is_array($config) && count($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }

    public function assign($key, $value = "") {
        if (empty($key) || !is_string($key)) {
            return false;
        }

        $this->data[$key] = $value;
        return true;
    }

    public function compile($str) {
        $str = str_replace("{/if}","<?php } ?>",$str);
        $str = str_replace("{else}", "<?php }else{ ?>",$str);
        $str = str_replace("{/foreach}","<?php \t}\n} ?>",$str);

        //$str = preg_replace_callback('/\s+(href|src)="([\w\d\.][\w\d\.\/\-]*)\.(js|css|png|jpg|gif)"/i', array(&$this, 'makeResourceReplace'), $str);

        $match = array();
        if (preg_match_all('/{(\$[a-zA-Z_][a-zA-Z0-9_\.]*)}/',$str,$match,PREG_SET_ORDER)) {
            foreach ($match as $m) {
                $str = str_replace($m[0], "<?php echo ".$this->transDotToArray($m[1])."; ?>",$str);
            }
        }

        if(preg_match_all('/{(else)?if\s+([^}]+)}/', $str, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                $replace = "<?php ";
                if($m[1]) {
                    $replace .= "} else ";
                }
                $replace .= "if(".$this->parseCondition($m[2]).") { ?>";

                $str = str_replace($m[0], $replace, $str);
            }
        }

        if (preg_match_all('/{foreach\s+(\$[a-zA-Z_][a-zA-Z0-9_\.]*)\s+as\s+(\$[a-zA-Z_][a-zA-Z0-9_]*)\s*(=>)?\s*(\$[a-zA-Z_][a-zA-Z0-9_]*)*\s*}/', $str, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                $replace = "<?php if(is_array(".$this->transDotToArray($m[1]).")) {\n"
                    . "\t\$_row = 0;\n\t\$_rowOffset = 0;\n\t"
                    . "foreach(".$this->transDotToArray($m[1])." as ".$m[2];
                if ($m[3]) {
                    $replace .= " => ".$m[4];
                }
                $replace .= ") { \n ";
                $replace .= "\t\t\$_row++;\n\t\t\$_rowOffset = \$_row - 1;\n\t\t\$_bit = \$_row % 2; ?>\n";

                $str = str_replace($m[0], $replace, $str);
            }
        }

        $str = preg_replace('/{eval\s+([^}]+)}/', "<?php \$1 ?>", $str);

        return $str;
    }

    public function transDotToArray($str) {
        if (($pos = strpos($str,".")) == false) {
            return $str;
        }

        $head = substr($str,0,$pos);
        $tail = substr($str, $pos + 1);
        $tail = str_replace(".","\"][\"",$tail);

        return $head."[\"".$tail."\"]";
    }

    public function parseCondition($condition) {
        if(empty($condition)) {
            return "";
        }
         //only replace the array vars
        if (preg_match_all('/(\$[a-z_][a-z_0-9]*\.[a-z_0-9\.]*)/i', $condition, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $condition = str_replace($m[0], $this->transDotToArray($m[1]), $condition);
            }
        }

        return $condition;
    }

    public function display($data = array()) {
        $data = array_merge($this->data, $data);

        if (empty($this->tpl)) {
            throw new Exception("Need template file name!", 501);
        }

        $tpl = $this->tpl.$this->config["suffix"];

        $includeFile = $this->makeCachePath($tpl);
        while ($tpl) {
            $templateFile = $this->makeTplFullPath($tpl);
            if(!file_exists($templateFile)) {
                throw new Exception("File $templateFile is not exist!", 502);
            }
            $str = file_get_contents($this->makeTplFullPath($tpl));

            if (preg_match_all('/{template "?([a-zA-Z0-9_\.\/\\\]+)"?}/', $str, $match, PREG_SET_ORDER)) {
                foreach ($match as $m) {
                    array_push($this->includeStack, $m[1]);
                    $str = str_replace($m[0], "<?php include '".$this->makeCachePath($m[1])."'; ?>", $str);
                }
            }

            if ($this->checkCacheFileTime($tpl)) {
                $cacheContent = $this->compile($str);
                file_put_contents($this->makeCachePath($tpl), $cacheContent);
            }

            $tpl = array_pop($this->includeStack);
        }

        if ($data) {
            extract($data);
        }

        include $includeFile;
    }

    public function output($tpl, $data = array()) {
        $this->tpl = $tpl;
        $this->display($data);
    }

    public function checkCacheFileTime($tpl) {
        $templateFile = $this->makeTplFullPath($tpl);
        $cacheFile = $this->makeCachePath($tpl);
        if (!file_exists($cacheFile)) {
            return true;
        }

        return filemtime($templateFile) > filemtime($cacheFile) ? true : false;
    }

    public function makeTplFullPath($tpl) {
        return APP_ROOT.DIRECTORY_SEPARATOR.
            $this->config["template_dir"].DIRECTORY_SEPARATOR.$tpl;
    }

    public function makeCachePath($tpl) {
        return APP_ROOT.DIRECTORY_SEPARATOR.
            $this->config["cache_dir"].DIRECTORY_SEPARATOR.
            str_replace(array("\\","/"),"_", $tpl).".php";
    }

    public function makeResourceReplace($t) {
        if(strpos($t[1],'http://') !== 0 || strpos($t[1],'/') !== 0) {
            return ' '.$t[1].'="'.$this->config['template_dir'].'/'.$t[2].".".$t[3].'"';
        }
        return $t[1];
    }
}

