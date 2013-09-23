<?php
/* this file used to operate share memory to get high performance
   author : yiyuanjian@gmail.com
*/
class Asf_ShareMemory {

    /*proj should not be modify. it's hard code.
      it should be accessed by callers.*/
    const PROJ = '~';

    const SHM_ROOT = "/dev/shm/asf_shm_";

    private $key;
    private $perm = 0600;
    private $len = 65536;

    private $tmpDir = "/tmp/";



    private $shmId;

    private $path; // ftok will use


    public function __construct($pathname, $perm = 0600, $len = 65536) {
        if(empty($pathname) || !file_exists($pathname)) {
            throw new Asf_Exception("File $pathname is not exist.", 0x2101);
            return null;
        }
        $this->setPath($pathname);
        $this->setPerm($perm);
        $this->setLen($len);

        return ;
    }

    public function getKey() {
        return $this->key;
    }

    public function setTmpDir($dir) {
        $this->tmpDir = $dir;
        return true;
    }

    public function setPath($pathname) {
        $this->path = $pathname;

        $this->key = $this->_ftok();
        return true;
    }

    public function setPerm($perm) {
        $this->perm = $perm;
        return true;
    }

    public function setLen($len) {
        if($len < 1) {
            throw new Exception("Share memory should have 1 chars at least");
            return false;
        }

        $this->len = $len;
        return true;
    }


    public function open() {
        $this->shmId = $this->shmopOpen($this->key, "c", $this->perm, $this->len);
        if(!$this->shmId) {
            throw new Exception("open shm with key ".$this-key." failed");
            return false;
        }

        return true;
    }

    public function close() {
        if($this->shmId > 0) {
            $this->shmopClose($this->shmId);
            $this->shmId = 0;
        }

        return true;
    }

    public function delete() {
        //perhaps not need read or write, just delete it
        if(!$this->shmId) {
            $id = $this->shmopOpen($this->key, "w", $this->perm, $this->len);
            if(!$id) {
                return true;
            }
            return $this->shmopDelete($id);
        }

        $res = $this->shmopDelete($this->shmId);
        $this->shmId = 0;
        return $res;
    }

    public function getAll() {
        if(!$this->shmId) { //lazy open
            $this->open();
        }

        $data = $this->shmopRead($this->shmId, 0, $this->len);

        return ord($data) ? unserialize($data) : array();
    }

    public function setAll($data) {
        $data = serialize($data);

        if(strlen($data) >= $this->len) {
            throw new Exception("Data is larger than SHM segement");
            return false;
        }

        if(!$this->shmId) { //lazy open
            $this->open();
        }

        return $this->shmopWrite($this->shmId, $data, 0);
    }

    public function getByKey($key) {
        $data = $this->getAll();

        return isset($data[$key]) ? $data[$key] : null;
    }

    public function setByKey($key, $value) {
        $data = $this->getAll();

        $data[$key] = $value;

        return $this->setAll($data);
    }

    public function deleteByKey($key) {
        $data = $this->getAll();
        if(isset($data[$key])) {
            unset($data[$key]);
            return $this->setAll($data);
        }

        return true;
    }

    private function _ftok() {
        if(function_exists("ftok")) {
            return ftok($this->path, self::PROJ);
        }

        $s = stat($this->path);
        return sprintf("%u", (($s['ino'] & 0xffff) | (($s['dev'] & 0xff) << 16)
                        | (($this->proj & 0xff) << 24)));
    }

    public function lock() {
        if(function_exists('sem_get')) {
            $key = $this->_ftok();
            $fp = sem_get($key, 1, 0600);
            sem_acquire($fp);

            return $fp;
        }

        $fp = @fopen($this->tmpDir."/php_".md5($this->path).".lock","wb");
        if(!$fp) {
            throw new Exception("can't open ".$this->tmpDir."/php_".md5(__FILE__).".lock");
            return false;
        }
        flock($fp, LOCK_EX);

        return $fp;
    }

    public function unlock(&$lock) {
        if(function_exists("sem_get")) {
            sem_release($lock);
        } else {
            flock($lock, LOCK_UN);
            fclose($lock);
        }

        return true;
    }

    public function __destruct() {
        $this->close();
    }

    private function shmopOpen($key, $tag, $perm, $len) {
        if(function_exists('shmop_open')) {
            return shmop_open($key, $tag, $perm, $len);
        }

        if(file_exists(self::SHM_ROOT.$key)) {
            return fopen(self::SHM_ROOT.$key, "r+b");
        }

        return fopen(self::SHM_ROOT.$key, "w+b");
    }

    private function shmopDelete($id) {
        if(function_exists('shmop_delete')) {
            return shmop_delete($id);
        }

        if(file_exists(self::SHM_ROOT.$this->key)) {
            return unlink(self::SHM_ROOT.$this->key);
        }
    }

    private function shmopRead($id, $offset, $len) {
        if(function_exists('shmop_read')) {
            return shmop_read($id, $offset, $len);
        }

        fseek($id, $offset);
        return fread($id, $len);
    }

    private function shmopWrite($id, $data, $offset) {
        if(function_exists('shmop_write')) {
            return shmop_write($id, $data, $offset);
        }

        fseek($id, $offset);
        return fwrite($id, $data);
    }

    private function shmopClose($id) {
        if(function_exists('shmop_close')) {
            return shmop_close($id);
        }

        return fclose($id);
    }
}
