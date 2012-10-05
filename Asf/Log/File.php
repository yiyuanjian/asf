<?php

/**
 * @author yuanjian
 *
 */
class Asf_Log_File extends Asf_Log_Abstract implements Asf_Log_Interface {

    private $logFile;

    public function __construct($category, $path, $splitByDay = 1) {
        $this->category = $category;
        $this->path = $path;
        $this->splitByDay = $splitByDay;

        //make sure all log in one request will save in same file.
        $this->logFile = $this->splitByDay ?
                $this->path."/".$this->category."_".date('Y-m-d').".log" :
                $this->path."/".$this->category.".log";

        return;
    }

    public function log($msg, $level, $addTime = 1, $addLevel = 1) {
        if(is_array($msg)) {
            $msg = implode($this->separation, $msg);
        }

        $preMsg = "";
        if($addTime) {
            $preMsg = $this->splitByDay ?
                      $this->getMicroTime() :
                      $this->getDateMircoTime();
            $preMsg .= $this->separation;
        }
        if($addLevel) {
            $preMsg .= isset($this->logMap[$level]) ? $this->logMap[$level] : "[UNDEF]";
            $preMsg .= $this->separation;
        }

        error_log($preMsg.$msg."\n", 3, $this->logFile);

    }
}
