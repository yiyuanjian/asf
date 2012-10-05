<?php

/**
 * @author yuanjian
 *
 */
class Asf_Crypt_Des extends Asf_Crypt_Abstract implements Asf_Crypt_Interface {
    public static function encrypt($text, $key, $visual = '') {
        if(empty($key) || empty($text)) {
            throw new Asf_Crypt_Exception("Not value or key assigned");
            return false;
        }

        $block = mcrypt_get_block_size('des','ecb');
        $pad = $block - (strlen($text) % $block);

        $text .= str_repeat(chr($pad), $pad);

        $encrypted = mcrypt_encrypt(MCRYPT_DES, $key, $text, MCRYPT_MODE_ECB);

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

        $decrypted = mcrypt_decrypt(MCRYPT_DES, $key, $encrypted, MCRYPT_MODE_ECB);
        $block = mcrypt_get_block_size('des','ecb');
        $pad = ord(substr($decrypted, -1));
        $decrypted = substr($decrypted, 0, strlen($decrypted) - $pad);


        return $decrypted;
    }
}
