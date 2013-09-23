<?php

/**
 * @author yuanjian
 *
 */
abstract class Asf_Log_Abstract {

    protected $logMap = array("[EMERG]","[ALERT]", "[CRITI]","[ERROR]", "[WARNI]","[NOTIC]","[INFOR]","[DEBUG]", "[MESSA]", "[EXCEP]");

    protected $splitByDay = true;

    protected $category;

    protected $path;

    protected $separation = "\t";

    protected function getTime() {
        return date('H:i:s');
    }

    protected function getDate() {
        return date('Y-m-d');
    }

    protected function getDateTime() {
        return date('Y-m-d H:i:s');
    }

    protected function getDateMircoTime() {
        list($sec, $usec) = explode(".", sprintf("%.6f", microtime(true)));

        return date('Y-m-d H:i:s', $sec).".".$usec;
    }

    protected function getMicroTime() {
        list($sec, $usec) = explode(".", sprintf("%.6f", microtime(true)));

        return date('H:i:s', $sec).".".$usec;
    }

    public function setSeparation($char = " ") {
        $this->separation = $char;

        return true;
    }

    public function getSeparation() {
        return $this->separation;
    }

    //abstract public function log($msg, $level, $time = 1, $level = 1);

    public function logEmergecy($msg, $addTime = 1, $addLevel = 1) {
        return $this->log($msg, Asf_Log::LOG_EMERG, $addTime, $addLevel);
    }

    public function logAlert($msg, $addTime = 1, $addLevel = 1) {
        return $this->log($msg, Asf_Log::LOG_ALERT, $addTime, $addLevel);
    }

    public function logCritical($msg, $addTime = 1, $addLevel = 1) {
        return $this->log($msg, Asf_Log::LOG_CRITICAL, $addTime, $addLevel);
    }

    public function logError($msg, $addTime = 1, $addLevel = 1) {
        return $this->log($msg, Asf_Log::LOG_ERROR, $addTime, $addLevel);
    }

    public function logWarning($msg, $addTime = 1, $addLevel = 1) {
        return $this->log($msg, Asf_Log::LOG_WARNING, $addTime, $addLevel);
    }

    public function logNotice($msg, $addTime = 1, $addLevel = 1) {
        return $this->log($msg, Asf_Log::LOG_NOTICE, $addTime, $addLevel);
    }

    public function logInfo($msg, $addTime = 1, $addLevel = 1) {
        return $this->log($msg, Asf_Log::LOG_INFO, $addTime, $addLevel);
    }

    public function logDebug($msg, $addTime = 1, $addLevel = 1) {
        return $this->log($msg, Asf_Log::LOG_DEBUG, $addTime, $addLevel);
    }

    public function logMsg($msg, $addTime = 0, $addLevel = 0) {
        return $this->log($msg, Asf_Log::LOG_MESSAGE, $addTime, $addLevel);
    }

    public function logException(Exception $e, $addTime = 1, $addLevel = 1) {
        return $this->log($e->__toString(), Asf_Log::LOG_EXCEPTION, $addTime, $addLevel);
    }
}
