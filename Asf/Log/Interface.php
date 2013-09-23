<?php

/**
 * @author yuanjian
 *
 */
interface Asf_Log_Interface {
    public function setSeparation($char = " ");
    public function log($msg, $level, $time = 1, $level = 1);
    public function logEmergecy($msg, $time = 1, $level = 1);
    public function logAlert($msg, $time = 1, $level = 1);
    public function logCritical($msg, $time = 1, $level = 1);
    public function logError($msg, $time = 1, $level = 1);
    public function logWarning($msg, $time = 1, $level = 1);
    public function logNotice($msg, $time = 1, $level = 1);
    public function logInfo($msg, $time = 1, $level = 1);
    public function logDebug($msg, $time = 1, $level = 1);
    public function logMsg($msg, $time = 0, $level = 0);
    public function logException(Exception $e, $time = 1, $level = 1);
}
