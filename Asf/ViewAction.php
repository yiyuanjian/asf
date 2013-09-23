<?php
abstract class Asf_ViewAction {
    protected static function output($templateFilename, $data = array(), $autoExit = 1) {
        $tpl = new Asf_Template($templateFilename);
        $tpl->display($data);
        
        return $autoExit && exit();
    }
    
    protected static function display($templateFilename, $data = array()) {
        return self::output($templateFilename, $data, 0);
    }
}