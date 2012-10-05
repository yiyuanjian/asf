<?php

/**
 * @author yuanjian
 *
 */
class Asf_Rdb_Oracle extends Asf_Rdb_Abstract implements Asf_Rdb_Interface {
    const DEFAULT_PORT = 1521;
    const DEFAULT_CHARSET = "al32utf8";

    private $host = null;
    private $port = 1521;
    private $user = null;
    private $password = null;
    private $dbname = "";
    private $charset = "";

    private $connectId = null;
    private $queryId = null;

    public function __construct($confs = array()) {
        if($confs) {
            $this->host = isset($confs['host']) ? $confs['host'] : null;
            $this->port = isset($confs['port']) && $confs['port'] ? $confs['port'] : self::DEFAULT_PORT;
            $this->user = isset($confs['user']) ? $confs['user'] : null;
            $this->password = isset($confs['password']) ? $confs['password'] : null;
            $this->dbname = isset($confs['dbname']) ? $confs['dbname'] : "";
            $this->charset = isset($confs['charset']) ? $confs['charset'] : self::DEFAULT_CHARSET;
        }

        return;
    }

    public function connect() {

        $connStr = "(description=(address=(protocol=tcp)(host=".$this->host.")".
                "(port=".$this->port."))(connect_data=(service_name=".$this->dbname.")))";

        $this->connectId = oci_connect($this->user, $this->password, $connStr, $this->charset);

        if(!$this->connectId) {
            throw new Asf_Rdb_Exception("connect to oracle $connStr failed", 0x0201);
            return null;
        }

        return $this->connectId;
    }

    public function close() {
        return $this->connectId ? oci_close($this->connectId) : true;
    }

    public function prepare() {

    }

    public function query($sql) {
        if(!$this->connectId) {
            $this->connect();
        }

        $this->queryId = oci_parse($this->connectId, $sql);
        if(!$this->queryId) {
            throw new Asf_Rdb_Exception("oci_parse failed", 0x0202);
            return null;
        }

        $res = @oci_execute($this->queryId, OCI_COMMIT_ON_SUCCESS);
        if(!$res) {
            $err = oci_error($this->queryId);
            throw new Asf_Rdb_Exception("oci_execute failed for $sql, ". $err['message'], 0x0203);
            return null;
        }

        return $this->queryId;
    }

    public function fetchSingleValue($sql) {
        $queryId = $this->query($sql);
        if($queryId == null) return null;

        $row = oci_fetch_row($queryId);

        if (empty($row) || !is_array($row)) {
            return null;
        }

        return $row[0];
    }

    public function fetchOneRow($sql, $mode = 1) {
        $res = $this->query($sql);
        if($res == null) return null;

        return oci_fetch_assoc($res);
    }

    public function fetchAll($sql, $maxRows = 1000, $mode = 1) {
        $queryId = $this->query($sql);
        if($queryId == null) return null;

        $data = array();
        $i = 0;
        while(($row = oci_fetch_assoc($queryId)) && (!$maxRows || $i <= $maxRows)) {
            $data[] = $row;
            $i++;
        }

        return $data;
    }

    public function fetchOneByOne($res) {
        return oci_fetch_assoc($res);
    }

    public function commit() {

    }

    public function rollback() {

    }
}
