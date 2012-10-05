<?php

class Asf_Rdb_Mysql extends Asf_Rdb_Abstract implements Asf_Rdb_Interface {
    private $host = null;
    private $port = 3306;
    private $user = null;
    private $password = null;
    private $dbname = "";
    private $charset = "utf8";


    public function __construct($confs = array()) {
        if($confs) {
            $this->host = isset($confs['host']) ? $confs['host'] : null;
            $this->port = isset($confs['port']) ? $confs['port'] : 3306;
            $this->user = isset($confs['user']) ? $confs['user'] : null;
            $this->password = isset($confs['password']) ? $confs['password'] : null;
            $this->dbname = isset($confs['dbname']) ? $confs['dbname'] : "";
            $this->charset = isset($confs['charset']) ? $confs['charset'] : "utf8";
        }

        return;
    }

    public function connect() {
        if(empty($this->conn)) {
            $conn = mysql_connect($this->host.':'.$this->port,
                        $this->user, $this->password);
            if(!$conn) {
                throw new Asf_Rdb_Exception("conn to host failed\n",
                            Asf_Rdb_Exception::ERR_CONNECT_FAILED);
                return null;
            }
            if($this->dbname) {
                if(!mysql_select_db($this->dbname, $conn)) {
                    throw new Exception("select db $this->dbname failed");
                }
            }
            $this->charset && mysql_set_charset($this->charset, $conn);

            $this->conn = $conn;
        }

        return $this->conn;
    }

    public function close() {
        return $this->conn && mysql_close($this->conn);
    }

    public function prepare() {

    }

    public function query($sql) {
        if(!$this->conn) {
            $this->connect();
        }

        $res = mysql_query($sql, $this->conn);

        if($res === false) {
            throw new Asf_Rdb_Exception("Query $sql failed: ".mysql_errno($this->conn).": ".mysql_error($this->conn),
                        Asf_Rdb_Exception::ERR_QUERY_FAILED);
            return false;
        }

        $this->res = $res;
        return $this->res;
    }

    public function fetchSingleValue($sql) {
        $res = $this->query($sql);

        $row = mysql_fetch_row($res);

        return $row ? $row[0] : null;
    }

    public function fetchOneRow($sql, $mode = MYSQL_ASSOC) {
        $res = $this->query($sql);

        $row = mysql_fetch_array($res, $mode);

        return $row;
    }

    public function fetchAll($sql, $maxRows = 1000, $mode = MYSQL_ASSOC) {
        $res = $this->query($sql);

        $results = array();
        if($maxRows) {
            $count = 0;
            while(($row = mysql_fetch_array($res, $mode)) !== false) {
                $results[] = $row;
                $count ++;
                if($count == $maxRows) break;
            }
        } else {
            while(($row = mysql_fetch_array($res, $mode)) !== false) {
                $results[] = $row;
            }
        }

        return $results;
    }

    public function fetchOneByOne($res, $mode = MYSQL_ASSOC) {
        if(!is_resource($res)) {
            throw new Asf_Rdb_Exception("argument 1 is not available resource");
            return null;
        }

        return mysql_fetch_array($res, $mode);
    }

    public function commit() {

    }

    public function rollback() {

    }
}
