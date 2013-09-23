<?php

/**
 * @author yuanjian
 *
 */
abstract class Asf_Crypt_Abstract {

    protected static function visual($binary, $method) {
        switch ($method) {
            case 'base64':
                return base64_encode($binary);
                break;
            case 'base64i':
                return Asf_Encode_Base64i::encode($binary);
                break;
            case 'hex':
                return Asf_Encode_Hex::encode($binary);
                break;
            case 'HEX':
                return Asf_Encode_Hex::encodeUpper($binary);
                break;
            default:
                throw new Asf_Encode_Exception("Not support method $method",
                    Asf_Encode_Exception::NOT_SUPPORT_METHOD);
            break;
        }
    }

    protected static function unvisual($string, $method) {
        switch ($method) {
            case 'base64':
                return base64_decode($string);
                break;
            case 'base64i':
                return Asf_Encode_Base64i::decode($string);
                break;
            case 'hex':
                return Asf_Encode_Hex::decode($string);
                break;
            case 'HEX':
                return Asf_Encode_Hex::decodeUpper($string);
                break;
            default:
                throw new Asf_Encode_Exception("Not support encode method $method",
                Asf_Encode_Exception::NOT_SUPPORT_METHOD);
                break;
        }
    }

    protected static function serial($array, $method = 'serial') {
        switch ($method) {
            case 'serial':
                return serialize($array);
                break;
            case 'json':
            case 'jsona':
            case 'jsono':
                return json_encode($array);
                break;
            default:
                throw new Asf_Crypt_Exception("Not support serial method $method",
                    Asf_Crypt_Exception::NOT_SUPPORT_METOD);
                break;
        }
    }

    protected static function unserial($serialed, $method = 'serial') {
        switch ($method) {
            case 'serial':
                return unserialize($serialed);
                break;
            case 'json':
            case 'jsona':
                $unserialed = @json_decode($serialed, true);
                if($unserialed == null) {
                    throw new Asf_Crypt_Exception("json_decode failed", -1);
                    return null;
                }

                return $unserialed;
                break;
            case 'jsono':
                $unserialed = @json_decode($serialed);
                if($unserialed == null) {
                    throw new Asf_Crypt_Exception("json_decode failed", -1);
                    return null;
                }

                return $unserialed;
                break;
            default:
                throw new Asf_Crypt_Exception("Not support unserial method '$method'",
                    Asf_Crypt_Exception::NOT_SUPPORT_METOD);
                break;
        }
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
