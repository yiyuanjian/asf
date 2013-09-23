<?php

/**
 * @author yuanjian
 *
 */
class Asf_Crypt_3des extends Asf_Crypt_Abstract implements Asf_Crypt_Interface {

    private static function pkcs5Pad ($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    private static function pkcs5Unpad($text){
        $pad = ord($text{strlen($text)-1});

        if ($pad > strlen($text)) {
            return false;
        }

        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad){
            return false;
        }

        return substr($text, 0, -1 * $pad);
    }

    private static function PaddingPKCS7($data) {
        $block_size = mcrypt_get_block_size(MCRYPT_3DES, MCRYPT_MODE_CBC);
        $padding_char = $block_size - (strlen($data) % $block_size);
        $data .= str_repeat(chr($padding_char),$padding_char);
        return $data;
    }

    public static function encrypt($text, $key, $visual = '') {
        if(empty($key) || empty($text)) {
            throw new Asf_Crypt_Exception("Not value or key assigned");
            return false;
        }

        $size = mcrypt_get_block_size(MCRYPT_3DES,'ecb');
        $text = self::pkcs5Pad($text, $size);
        $key = str_pad($key,24,'0');
        $td = mcrypt_module_open(MCRYPT_3DES, '', 'ecb', '');
        $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        @mcrypt_generic_init($td, $key, $iv);
        $encrypted = mcrypt_generic($td, $text);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return $visual ? self::visual($encrypted, $visual) : $encrypted;
    }

    public static function decrypt($encrypted, $key, $visual = '') {
        if(empty($key) || empty($encrypted)) {
            throw new Asf_Crypt_Exception("Not encrypted or key assigned");
            return false;
        }

        if($visual) {
            $encrypted = self::unvisual($encrypted, $visual);
        }

        $key = str_pad($key,24,'0');
        $td = mcrypt_module_open(MCRYPT_3DES,'','ecb','');
        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td),MCRYPT_RAND);
        $ks = mcrypt_enc_get_key_size($td);
        @mcrypt_generic_init($td, $key, $iv);
        $decrypted = self::pkcs5Unpad(mdecrypt_generic($td, $encrypted));
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return $decrypted;
    }

    public static function encryptArray($array, $key,
                                $serial = "serial",
                                $visual = 'base64') {

        $string = self::serial($array, $serial);

        return self::encrypt($string, $key, $visual);
    }

    public static function decryptArray($crypted, $key,
                                $serial = "serial",
                                $visual = 'base64') {
        $string = self::decrypt($crypted, $key, $visual);

        $encrypted = self::unserial($string, $serial);

        return $encrypted;
    }
}
