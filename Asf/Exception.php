<?php
class Asf_Exception extends Exception {
    private $innerMessage = null;

    public function __construct($message, $code = null, $innerMessage = null, $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->innerMessage = $innerMessage;

        //TODO: write the exception Log
        if(defined("ASF_EXCEPTION_LOG_DIR")) {
            $file = ASF_EXCEPTION_LOG_DIR.DIRECTORY_SEPARATOR.'exception_'.date(Ymd).'.log';

            error_log($this->__toString(), 3, $file);
        }
    }

    public function getInnerMessage() {
        return $this->innerMessage == null ? $this->getMessage() : $this->innerMessage;
    }

    public function __toString() {
        $str = "Exception Code: ".$this->getCode() .". Message: ".$this->message."\n";
        $str .= "Actual Message: ".$this->getInnerMessage()."\n";
        $str .= "File: '".$this->getFile() ."' in line ".$this->getLine()."\n";
        $str .= "Call Trace: ".$this->getTraceAsString()."\n";

        return $str;
    }
}