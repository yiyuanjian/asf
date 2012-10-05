<?php

/** 
 * @author yuanjian
 * 
 */
abstract class Asf_ControllerAction {
    abstract public function indexAction();
    
    public function exitWithJson($code, $msg = "", $count = 0, $data = array()) {
        $ret['code'] = $code;
        $ret['msg'] = $msg;
        $ret['count'] = $count;
        $ret['data'] = $data;
        
        exit(json_encode($ret));
    }
}
