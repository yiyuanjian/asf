<?php
/**
 * serial method support serial, json
 * visual method support base64, base64i, hex, HEX
 * 
 * @author yuanjian
 *
 */
class Asf_Crypt_Xxtea extends Asf_Crypt_Abstract implements Asf_Crypt_Interface {
    public static function encrypt($str, $key, $visual = '') {
        if ($str == "") {
            return "";
        }
        $v = self::str2long($str, true);
        $k = self::str2long($key, false);
        if (count($k) < 4) {
            for ($i = count($k); $i < 4; $i++) {
                $k[$i] = 0;
            }
        }
        $n = count($v) - 1;
    
        $z = $v[$n];
        $y = $v[0];
        $delta = 0x9E3779B9;
        $q = floor(6 + 52 / ($n + 1));
        $sum = 0;
        while (0 < $q--) {
            $sum = self::int32($sum + $delta);
            $e = $sum >> 2 & 3;
            for ($p = 0; $p < $n; $p++) {
                $y = $v[$p + 1];
                $mx = self::int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
                $z = $v[$p] = self::int32($v[$p] + $mx);
            }
            $y = $v[0];
            $mx = self::int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
            $z = $v[$n] = self::int32($v[$n] + $mx);
        }
    
        if ($visual) {
            return self::visual(self::long2str($v, false), $visual);
        }
        
        return self::long2str($v, false);
    }
    
    public static function decrypt($str, $key ,$visual = '') {
        if ($str == "") {
            return "";
        }
        if ($visual) {
            $v = self::str2long(self::unvisual($str, $visual), false);
        } else {
            $v = self::str2long($str, false);
        }
    
        $k = self::str2long($key, false);
        if (count($k) < 4) {
            for ($i = count($k); $i < 4; $i++) {
                $k[$i] = 0;
            }
        }
        $n = count($v) - 1;
    
        $z = $v[$n];
        $y = $v[0];
        $delta = 0x9E3779B9;
        $q = floor(6 + 52 / ($n + 1));
        $sum = self::int32($q * $delta);
        while ($sum != 0) {
            $e = $sum >> 2 & 3;
            for ($p = $n; $p > 0; $p--) {
                $z = $v[$p - 1];
                $mx = self::int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
                $y = $v[$p] = self::int32($v[$p] - $mx);
            }
            $z = $v[$n];
            $mx = self::int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
            $y = $v[0] = self::int32($v[0] - $mx);
            $sum = self::int32($sum - $delta);
        }
        return self::long2str($v, true);
    }
    
    public static function encryptArray($array, $key,
                                $serial = "serial",
                                $visual = 'base64') {
        return self::encrypt(self::serial($array, $serial), $key, $visual);
        
    }
    
    public static function decryptArray($crypted, $key,
                                $serial = "serial",
                                $visual = 'base64') {
        return self::unserial(self::decrypt($crypted, $key, $visual), $serial);
    }
    
    private static function str2long($s, $w) {
        $s_len = strlen($s);
        $v = array();
        for ($i = 0, $ss = $s_len; $i < $s_len;$i += 4,$ss -= 4) {
            if ($ss < 4) {
                $tmp = ord($s[$i]);
                if(isset($s[$i + 1])) $tmp |= ord($s[$i + 1]) << 8;
                if (isset($s[$i + 2])) $tmp |= ord($s[$i + 2]) << 16;
                if (isset($s[$i + 3])) $tmp |= ord($s[$i + 3]) << 24;
                $v[] = $tmp;
            } else {
                $v[] = ord($s[$i]) | ord($s[$i + 1]) << 8 | ord($s[$i + 2]) << 16 | ord($s[$i + 3]) << 24;
            }
        }
        if ($w) {
            $v[] = $s_len;
        }
    
        return $v;
    }
    
    private static function long2str($v, $w) {
        $v_len = count($v);
        $s = "";
        for ($i = 0; $i < $v_len; $i++) {
            $s .= chr($v[$i] & 0xff).chr($v[$i] >> 8 & 0xff).chr($v[$i] >> 16 & 0xff).chr($v[$i] >> 24 & 0xff);
        }
        if ($w) {
            $s_len = $v[$v_len - 1] & 0xffffffff;
            $s = substr($s,0,$s_len);
        }
    
        return $s;
    }
    
    private static function int32($n) {
        while ($n >= 2147483648) $n -= 4294967296;
        while ($n <= -2147483649) $n += 4294967296;
        return (int)$n;
    }
}
