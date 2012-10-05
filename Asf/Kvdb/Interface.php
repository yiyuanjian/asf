<?php

/**
 * @author yuanjian
 *
 */
interface Asf_Kvdb_Interface {

    public function setTimeout($timeout);

    public function get($key);

    public function set($key, $value);

    public function connect();

    public function close();

    public function flush();
}
