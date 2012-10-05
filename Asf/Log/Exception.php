<?php

/** 
 * @author yuanjian
 * 
 */
class Asf_Log_Exception extends Exception {
    // TODO - Insert your code here
    const NOT_SUPPORT_HANDLER = 1;
    const PARSE_CONF_FAILED = 2;
    
    const NOT_ASSIGN_ANY_HANDLER = 3;
    
    const GET_INSTANCE_FAILED = 4;
    
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }
}
