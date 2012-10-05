<?php
/** This file is used to creat a sample xls files that can be opened with 
 * mircosoft office excel 97 and older.
 * only support on sheet, only support 2 types,  number and text.
 * Author: Yuanjian Yi <yiyuanjian@gmail.com>
 * 
 * the method source from internet.
 */

class Asf_Excel_SampleWriter {
    private $binaryData = '';
    
    public function __construct() {
        //auto write file description to stream
        $this->writeBegin();
    }
    
    private function writeBegin() {
        $this->binaryData = pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
    }
    
    private function writeEnd() {
        $this->binaryData .= pack("ss", 0x0A, 0x00);
    }

    public function xlsWriteNumber($Row, $Col, $Value) {
        $this->binaryData .= pack("sssss", 0x203, 14, $Row, $Col, 0x0);
        $this->binaryData .= pack("d", $Value);
        return;
    }

    public function xlsWriteLabel($Row, $Col, $Value ) {
        $L = strlen($Value);
        $this->binaryData .= pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
        $this->binaryData .= $Value;
        return;
    }

    public function getBinaryData() {
        //auto add bof data to stream.
        $this->writeEnd();
        
        return $this->binaryData;
    }
    
    /*
     * write the data to a 
     */
    public function exportWithFile($filename) {
        file_put_contents($filename, $this->binaryData);
    }
}
