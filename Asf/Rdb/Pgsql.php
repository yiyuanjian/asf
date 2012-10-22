<?php
class Asf_Rdb_Pgsql extends Asf_Rdb_Abstract implements Asf_Rdb_Interface {
    const DEFAULT_PORT = 5432;

    public function __construct($confs = array()) {
        if($confs) {
            $this->host = isset($confs['host']) ? $confs['host'] : null;
            $this->port = isset($confs['port']) && $confs['port'] ? $confs['port'] : self::DEFAULT_PORT;
            $this->user = isset($confs['user']) ? $confs['user'] : null;
            $this->password = isset($confs['password']) ? $confs['password'] : null;
            $this->dbname = isset($confs['dbname']) ? $confs['dbname'] : "";
            $this->charset = isset($confs['charset']) ? $confs['charset'] : "utf8";
        }

        return;
    }

    public function connect() {
        if(empty($this->conn)) {
            $connStr = "host=$this->host port=$this->port dbname=$this->dbname user=$this->user password=$this->password";
            $conn = pg_connect($connStr);

            if(!$conn) {
                throw new Asf_Rdb_Exception("Connect to Pgsql failed", 0x01);
                return null;
            }

            $this->conn = $conn;
        }

        return $this->conn;
    }

    public function close() {
        return $this->conn && pg_close($this->conn);
    }

    public function prepare() {

    }

    public function query($sql = "") {
        if(!$this->conn) {
            $this->connect();
        }

        $sql = $this->checkSQL($sql);

        $res = pg_query($this->conn, $sql);

        if($res === false) {
            throw new Asf_Rdb_Exception("Query $sql failed: ".pg_last_error($this->conn),
                    Asf_Rdb_Exception::ERR_QUERY_FAILED);
            return false;
        }

        $this->res = $res;
        return $this->res;
    }

    public function checkSQL($sql) {
        if(!$sql && !$this->sql) {
            throw new Exception("SQL is empty!", 0x21);
            return null;
        }

        if(!$sql) {
            return $this->sql;
        }

        return $sql;
    }

    public function fetchSingleValue($sql = '') {
        $res = $this->query($sql);

        $row = pg_fetch_row($res);

        return $row ? $row[0] : null;
    }

    public function fetchOneRow($sql = '', $mode = PGSQL_ASSOC) {
        $res = $this->query($sql);

        $row = pg_fetch_array($res, null, $mode);

        return $row;
    }

    public function fetchAll($sql = '', $maxRows = 1000, $mode = PGSQL_ASSOC) {
        $res = $this->query($sql);

        $results = array();
        if($maxRows) {
            $count = 0;
            while(($row = pg_fetch_array($res, null, $mode)) !== false) {
                $results[] = $row;
                $count ++;
                if($count == $maxRows) break;
            }
        } else {
            while(($row = pg_fetch_array($res, $mode)) !== false) {
                $results[] = $row;
            }
        }

        return $results;
    }

    public function fetchOneByOne($res, $mode = PGSQL_ASSOC) {
        if(!is_resource($res)) {
            throw new Asf_Rdb_Exception("argument 1 is not available resource");
            return null;
        }

        return pg_fetch_array($res, null, $mode);
    }

    public function commit() {

    }

    public function rollback() {

    }


}