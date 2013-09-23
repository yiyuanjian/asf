<?php
class Asf_Download {
    public static function excel($filename, $data, $len, $exit = 1) {
        header("Content-type:application/vnd.ms-excel");
        header('Content-Length:'. $len);
        header("Content-Disposition:attachment;filename=$filename");
        header("Content-Transfer-Encoding: binary ");
        
        echo $data;
        
        if ($exit) {
            exit();
        }
    }
    
    public static function txt($filename, &$data, $exit = 1) {
        header("Content-type:text/text");
        header('Content-Length:'. strlen($data));
        header("Content-Disposition:attachment;filename=$filename");
        header("Content-Transfer-Encoding: text ");
        
        echo $data;
        
        if ($exit) {
            exit();
        }
    }
}
