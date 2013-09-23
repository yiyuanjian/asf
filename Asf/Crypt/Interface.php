<?php

/** 
 * @author yuanjian
 * 
 */
interface Asf_Crypt_Interface {
    public static function encrypt($value, $key, $visual = '');
    
    public static function decrypt($crypted, $key, $visual = '');
    
    public static function encryptArray($array, $key,
                                $serial = "serial",
                                $visual = '');
    
    public static function decryptArray($crypted, $key,
                                $serial = "serial",
                                $visual = '');
    
}
