<?php

/**
 * @author yuanjian
 *
 */
class Asf_Kvdb_Redis extends Asf_Kvdb_Abstract implements Asf_Kvdb_Interface {
    private static $connected;

    public function __construct($conf) {
        if (! isset($conf['host']) || ! isset($conf['port'])) {
            throw new Asf_Kvdb_Exception("Need host and port.", 0x10);
            return false;
        }

        $this->host = $conf['host'];
        $this->port = $conf['port'];

        $this->instance = new Redis();
        self::$connected = false;

        return $this->instance;
    }

    public function get($key) {
        $this->connect();
        return $this->redis->get($key);
    }

    public function set($key, $value) {
        $this->connect();
        return $this->redis->set($key, $value);
    }

    public function connect() {

        if (self::$connected == $this->host.$this->port) {
            return $this->instance;
        }

        if (! $this->instance->connect($this->host, $this->port, $this->timeout)) {
            throw new Exception("connect to redis " . $this->host . ":" . $this->port . " failed", 0x11);
        }

        $this->connected = $this->host.$this->port;
        return $this->instance;
    }

    public function close() {
        if ($this->connected) {
            $this->instance->close();
            $this->connected = "";
        }
    }

    public function flush() {

    }

    public function __call($method, $args) {

        if (! method_exists($this->instance, $method)) {
            throw new Asf_Kvdb_Exception("Can't call method '$method' of Redis, not exist", 0x12);
        }

        $this->connect();

        switch (count($args)) {
            case 1 :
                return $this->instance->$method($args[0]);
            case 2 :
                return $this->instance->$method($args[0], $args[1]);
            case 3 :
                return $this->instance->$method($args[0], $args[1], $args[2]);
            case 4 :
                return $this->instance->$method($args[0], $args[1], $args[2], $args[3]);
            case 5 :
                return $this->instance->$method($args[0], $args[1], $args[2], $args[3], $args[4]);
        }
    }

    public function __destruct() {
        $this->close();
    }
}

?>
