<?php

/** 
 * @author yuanjian
 * 
 */
abstract class Asf_Rdb_Abstract {
    protected $conn;
    protected $res;   //query result
    
    protected $errno;
    protected $error; //or use exception to replace it.
    
    public function getErrno() {
        return $this->errno;
    }
    
    public function getError() {
        return $this->error;
    }

}
