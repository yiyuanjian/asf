<?php

/**
 * @desc This class base on scribe php libray and thirft which designed and
 * implemented by facebook.
 * @author yuanjian
 *
 *
 */
class Asf_Log_Scribe extends Asf_Log_Abstract implements Asf_Log_Interface {
    const TIMEOUT = 5;
    const VERSION_1 = 0x80010000;

    const STR_CATEGORY = 1;
    const STR_MESSAGE = 2;

    const MSG_TYPE_CALL = 1;

    const TYPE_STOP = 0;
    const TYPE_STRING = 11;
    const TYPE_STRUCT = 12;
    const TYPE_LIST = 15;


    private $buf = "";
    private $buf_len = 0;
    private $handle;

    public function __construct($category, $path, $splitByDay = 1) {
        $this->category = $category;
        $this->path = $path;
        $this->splitByDay = $splitByDay;

        //ensure the buf is empty.
        $this->buf = "";
        $this->buf_len = 0;

        return;
    }

    private function writeToBuf($data, $len) {
        $this->buf .= $data;
        $this->buf_len += $len;

        return true;
    }

    private function flushBuf() {
        if(!$this->handle) {
            $this->connect();
        }

        $header = pack('N', $this->buf_len);
        $len = fwrite($this->handle, $header.$this->buf, $this->buf_len + 4);
        if($len == false) { //connect may be broken
            fclose($this->handle);
            $this->connect();
            $len = fwrite($this->handle, $header.$this->buf, $this->buf_len + 4);
        }

        $this->buf = "";
        $this->buf_len = 0;

        return $len ? true : false;
    }

    private function connect() {
        list($host, $port) = explode(":", $this->path);

        $errno = 0;
        $errstr = "";

        $handle = pfsockopen($host, $port, $errno, $errstr, self::TIMEOUT);
        if($handle === false) {
            throw new Asf_Log_Exception("connect to $host:$port failed. ERR: $errno:$errstr");
            return false;
        }

        $this->handle = $handle;
    }

    private function close() {
        if ($this->handle) {
            fclose($this->handle);
            $this->handle = null;
        }

        return true;
    }

    private function write32Int($data) {
        $this->writeToBuf(pack("N", $data), 4);
        return 4;
    }

    private function write16Int($data) {
        $this->writeToBuf(pack("n", $data), 2);
        return 2;
    }

    private function writeString($str) {
        $len = strlen($str);
        $this->writeToBuf(pack('N', $len), 4);
        $this->writeToBuf($str, strlen($str));

        return $len;
    }

    private function writeByte($byte) {
        $this->writeToBuf(pack('c', $byte), 1);
        return 1;
    }

    /*
     * @param mixed $msg, string or string array.
     * interface just need implement string.
     */
    private function writeScribeMsg($msg) {
        $count = 1;
        if(is_array($msg)) {
            $count = count($msg);
        } else {
            $count = 1;
            $msg = array($msg);
        }
        //{header
        $this->write32Int(self::VERSION_1 | self::MSG_TYPE_CALL);
        $this->writeString('Log');
        $this->write32Int(0); // serial number
        //}

        //{ filed
        $this->writeByte(self::TYPE_LIST);
        $this->write16Int(1);
        //}

        //{ list
        $this->writeByte(self::TYPE_STRUCT);
        $this->write32Int($count); //message count
        //}
        foreach($msg as $message) {
            //{{ category
            $this->writeByte(self::TYPE_STRING);
            $this->write16Int(self::STR_CATEGORY);
            $this->writeString($this->category);
            //}}

            //{{ message
            $this->writeByte(self::TYPE_STRING);
            $this->write16Int(self::STR_MESSAGE);
            $this->writeString($message);
            //}}
            $this->writeByte(self::TYPE_STOP);
        }

        //: listend, current 0
        //: filed end current 0
        $this->writeByte(self::TYPE_STOP); //filed stop
        //: struct end. current 0
        $this->flushBuf();
    }

    public function log($msg, $level, $addTime = 1, $addLevel = 1) {
        if(is_array($msg)) {
            $msg = implode($this->separation, $msg);
        }

        $preMsg = "";

        if($addTime) {
           $preMsg = $this->splitByDay ? date('H:i:s') : date('Y-m-d H:i:s');
           $preMsg .= $this->separation;
        }

        if ($addLevel) {
            $preMsg .= isset($this->logMap[$level]) ? $this->logMap[$level] : "[UNDEF]";
            $preMsg .= $this->separation;
        }

        $this->writeScribeMsg($preMsg.$msg."\n");
    }
}
