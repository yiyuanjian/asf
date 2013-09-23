<?php

final class Asf_Gdata {
    private static $gData;
    
    public static function get($key) {
        if(!is_string($key) || empty($key)) {
            return null;
        }
        
        if(isset(self::$gData[$key])) {
            return self::$gData[$key];
        }
        
        return null;
    }
    
    public static function set($key, $value) {
        if(!is_string($key) || empty($key)) {
            return false;
        }
        
        self::$gData[$key] = $value;
        
        return true;
    }
    
    public static function getKeys() {
        return array_keys(self::$gData);
    }
    
    public static function isKeyExist($key) {
        if (empty($key)) return false;
        
        return isset(self::$gData[$key]) ? true : false;
    }
}
