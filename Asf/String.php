<?php
/**
 * @author Yuanjian Yi (Rememebr) <yiyuanjian@gmail.com>
 * @copyright Yuanjian Yi
 * @license BSD
 *
 */
class Asf_String {
    /**
     * @example 
     * <code>
     *  echo PublicFunc::subStrByByte($testStr,8,13,'GB18030');
     * </code>
     * 
     * @param string $string
     * @param int $start
     * @param int $length
     * @param string $charCode
     * 
     * @return string
     */
    public static function subStrByByte($string, $length, $start = 0, $charCode = 'utf-8') {
        if (empty($string)) {
            return '';
        }
        
        $strLength = strlen($string);
        
        if ($start > $strLength) {
            return '';
        }
        
        if ($start == 0 && $length >= $strLength) {
            return $string;
        }
        
        $match = self::matchChars($string, $charCode);
        
        $wordsCount = count($match);
        
        $byteOffset = 0;
        $wordsOffset = 0;
        
        if ($start > 0) {
            for($i = 0; $byteOffset < $start; $i++) {
                $byteOffset += strlen($match[$i]);
            }
            $wordsOffset = $i;
        }
        
        if ($length >= $strLength - $byteOffset) {
            return implode('', array_slice($match,$wordsOffset));
        }
            
        //
        for ($i = $wordsOffset,$l = 0; $l <= $length && $i <= $wordsCount; $i++ ) {
            $l += strlen($match[$i]);
        }
        
        return implode('', array_slice($match,$wordsOffset,$i - $wordsCount - 1));
    }
    
    /**
     *
     * You can also use iconv_substr and mb_substr to cut string,if your system 
     * support them.
     *
     * @example 
     * <code>
     *  $testStr = "GB18030编码的";
     *  echo PublicFunc::subStrByWord($testStr,8,5,'GB18030');
     * </code>
     * 
     * @param string $str
     * @param int $length
     * @param int $start
     * @param string $charCode
     * @return string
     */
    public static function subStrByWord($string, $length, $start = 0, $charCode = 'utf-8') {
        
        if (empty($string)) {
            return '';
        }
        
        $match = self::matchChars($string, $charCode);
        
        //all count
        $wordsCount = count($match);
            
        //{{{
        if ($start > $wordsCount) {
            return '';
        }
        //}}}
        
        //{{{
        if ($start == 0 && $length >= $wordsCount) {
            return $string;
        }
        //}}}
            
        //{{{
        if ($length >= $wordsCount - $start) {
            return implode('', array_slice($match, $start));
        } else {
            return implode('', array_slice($match, $start, $length));
        }
        //}}}
        
        return '';
    }
    
    /**
     * Support 'UTF-8','GB18030','BIG5'
     *
     * @param string $string
     * @param string $charCode
     * @return mixed
     */
    public static function matchChars($string, $charCode) {
        $charCode = strtoupper($charCode);
        if (!in_array($charCode, array('UTF-8','GB18030','BIG5'))) {
            $charCode = 'UTF-8';
        }
        
        $matchs = array();
        
        //{{{UTF-8
        if ($charCode == 'UTF-8') {
            preg_match_all("/[\x01-\x7f]|".
                "[\xc2-\xdf][\x80-\xbf]|".
                "\xe0[\xa0-\xbf][\x80-\xbf]|".
                "[\xe1-\xef][\x80-\xbf][\x80-\xbf]|".
                "\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|".
                "[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/",
                 $string, $matchs);
        }
        //}}}
        
        //{{{gb18030,GB2312,gbk
        if ($charCode == 'GB18030') {
            $matchs = array();
            preg_match_all("/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]|".  //GB2312
                "[\x81-\xfe][\x40-\xfe]|".  //GBK
                "[\x81-\xfe][\x40-\x7e]|[\x81-\xfe][\x80-\xfe]|".   //GB18030 double
                "[\xe1-\xfe][\x30-\x39][\x81-\xfe][\x30-\x39]/",    //GB18030 quato
                 $string, $matchs);
            
        }
        //}}}
        
        //{{{BIG5
        if ($charCode == 'BIG5') {
            $matchs = array();
            preg_match_all("/[\x01-\x7f]|[\xA1-\xF9][\x40-\x7E]|".  
                "[\xA1-\xf9][\xA1-\xfe]/".  
                 $string, $matchs);
        }
        //}}}
        
        $match = $matchs[0];
        
        return $match;
    }
}
