<?php

/** 
 * @author yuanjian
 * 
 */
class Asf_Encode_Hex implements Asf_Encode_Interface {
    public static function encode($binary) {
        $hex='';
        $len = strlen($binary);
        for ($i=0; $i < $len; $i++){
            $ord = ord($binary[$i]);
            if($ord <= 15) {
                $hex .= '0'.dechex($ord);
            } else {
                $hex .= dechex($ord);
            }
        }
        return $hex;
    }
    
    public static function decode($string) {
        if(!is_string($string) || empty($string)) {
            return "";
        }
        
        $len = strlen($string);
        
        $pairLen = $len - ($len % 2);
        
        $ret = '';
        for ($i = 0; $i < $pairLen - 1; $i += 2){
            $ret .= chr(hexdec($string[$i].$string[$i+1]));
        }
        
        if($pairLen < $len) {
            $ret .= chr(hexdec($string[$pairLen]));
        }
        
        return $ret;
    }
    
    public static function encodeUpper($binary) {
        return strtoupper(self::encode($binary));
    }
    
    public static function decodeUpper($string) {
        return self::decode(strtolower($string));
    }
}
