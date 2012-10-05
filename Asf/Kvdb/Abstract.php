<?php

/**
 *
 * @author yuanjian
 *
 */
abstract class Asf_Kvdb_Abstract {
    protected $host;
    protected $port;
    protected $timeout = 5;

    protected $instance; // instance for Key-value database instance;

    public function setTimeout($timeout) {
        $this->timeout = intval($timeout);
        return true;
    }

}
