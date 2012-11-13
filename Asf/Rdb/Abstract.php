<?php

/**
 * @author yuanjian
 *
 */
abstract class Asf_Rdb_Abstract {
    protected $host = null;
    protected $port = 0;
    protected $user = null;
    protected $password = null;
    protected $dbname = "";
    protected $charset = "utf8";

    protected $sql = '';

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


    public function select($cols) {
        $this->sql = "SELECT $cols ";

        return $this;
    }

    public function from($tabls) {
        $this->sql .= "FROM $tabls ";

        return $this;
    }

    public function where($condition) {
        $this->sql .= "WHERE $condition ";

        return $this;
    }

    public function groupBy($group) {
        $this->sql .= "GROUP BY $group ";

        return $this;
    }

    public function orderBy($order, $sequece = 'ASC') {
        if (in_array(strtoupper($sequece), array('ASC', 'DESC'))) {
            $this->sql .= "ORDER BY $order $sequece";
        }

        return $this;
    }

    public function limit($count, $offset) {
        $this->sql .= "LIMIT $count OFFSET $offset";

        return $this;
    }

    public function update($table, $fileds, $condition = '') {
        $sql = "UPDATE $table set ";
        $updateFileds = array();
        foreach ($fileds as $k => $v) {
            $updateFileds[] = "$k='$v'";
        }
        $sql .= implode(",", $updateFileds);
        unset($updateFileds);
        if($condition) {
            $sql .= "WHERE $condition";
        }

        return $this->query($sql);
    }

    public function insert($table, $fileds = array()) {
        $keys = array();
        $values = array();

        foreach ($fileds as $key => $value) {
            $keys[] = $key;
            $values[] = "'$value'";
        }

        $sql = "INSERT INTO $table(".implode(", ", $keys).") VALUES(".
                implode(",",$values).")";

        return $this->query($sql);
    }

    protected function getSQL($sql) {
        if(!$sql && !$this->sql) {
            throw new Asf_Rdb_Exception("SQL is empty!", 0x21);
            return null;
        }

        if(!$sql) {
            return $this->sql;
        }

        return $sql;
    }
}
