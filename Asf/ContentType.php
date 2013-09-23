<?php
class Asf_ContentType {
    private static $types = array(
        "_default" => "text/html",
        "json" => "application/json",
        "xml" => "application/xml",
        "mp4" => "video/mp4",
        "jpg" => "image/jpeg",
        "jpeg" => "image/jpeg",
        "png" => "image/png",
    );

    /*
     * @return boolean
     */
    public static function setType($suffix, $type) {
        if(empty($suffix) || empty($type)) {
            return false;
        }

        if(!strpos($type, '/')) {
            return false;
        }

        self::$types[$suffix] = $type;

        return true;
    }

    /*
     * @return string
     */
    public static function getType($suffix) {
        if(empty($suffix)) {
            return '';
        }

        if (!isset(self::$types[$suffix])) {
            return self::$types['_default'];
        }

        return self::$types[$suffix];
    }

    public static function setHeaderBySuffix($suffix) {
        if(empty($suffix)) {
            return false;
        }

        if (!isset(self::$types[$suffix])) {
            header('Content-Type: '.self::$types['_default']);
            return true;
        }

        header('Content-Type: '.self::$types[$suffix]);
        return true;
    }
}