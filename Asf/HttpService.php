<?php
/*
 * This file is used to call http serverice. based on curl
 */
class Asf_HttpService {
    public static function call($url, $datas = '', $timeout = 5,
                $outheader = false) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if($datas) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
        }

        if($outheader) {
            curl_setopt($ch, CURLOPT_HEADER, true);
        }

        $ret = @curl_exec($ch);

        $execCode = curl_errno($ch);
        if($execCode != 0) {
            throw new Asf_Exception("call remote $url, POST: ".var_export($datas, true)." failed: ".curl_error($ch), $execCode);
            return NULL;
        }

        return $ret;
    }
}
