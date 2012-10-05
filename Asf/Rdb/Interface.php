<?php

interface Asf_Rdb_Interface {
    public function connect();
    
    public function close();
    
    public function prepare();
    
    public function query($sql);
    
    public function fetchSingleValue($sql);
    
    public function fetchOneRow($sql, $mode = 1);
    
    public function fetchAll($sql, $maxRows = 1000, $mode = 1);
    
    public function fetchOneByOne($res);
    
    public function commit();
    
    public function rollback();
}
