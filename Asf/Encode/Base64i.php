<?php

/** 
 * @author yuanjian
 * 
 */
class Asf_Encode_Base64i implements Asf_Encode_Interface {
    public static function encode($binary) {
        return str_replace(array('+', '/', '='), array('-', '_', '.'), 
                        base64_encode($binary));
    }
    
    public static function decode($string) {
        return base64_decode(str_replace(array('-', '_', '.'), 
                                array('+', '/', '='),$string));
    }
}
