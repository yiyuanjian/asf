<?php
class Asf_ScriptAction {
    protected $outputHandler = STDOUT;

    public function setOutputHandler($handler) {
        if(!is_resource($handler)) {
            throw new Asf_Exception("Argument handler is not resource");
            return false;
        }

        $this->outputHandler = $handler;
    }

    public function output($string) {
        return fwrite($this->outputHandler, $string);
    }
}