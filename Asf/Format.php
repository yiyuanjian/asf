<?php

/**
 * @author yuanjian
 *
 */
class Asf_Format {
    // TODO - Insert your code here

    public static function str2Standard($str, $firstUp = false) {
        if(empty($str) || !preg_match('/^[A-Za-z\-]+$/', $str)) {
            return "";
        }

        $str = ucwords(str_replace("-", " ", strtolower($str)));
        $str = str_replace(' ', '', $str);

        if(!$firstUp) {
            if(function_exists("lcfirst")) {
                return lcfirst($str);
            } else {
                return strtolower(substr($str,0,1)).substr($str, 1);
            }
        }

        return $str;
    }

    public static function hashCode($string) {
        $sum = 5381;

        $len = strlen($string);
        for($i = 0; $i < $len; $i++) {
            $sum = ($sum << 5) + $sum + ord($string[$i]);
        }

        return $sum;
    }

}
