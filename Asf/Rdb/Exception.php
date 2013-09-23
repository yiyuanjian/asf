<?php

/**
 * @author yuanjian
 *
 */
class Asf_Rdb_Exception extends Exception {
    const ERR_NOT_SUPPORT_HANDLER = 1;
    const ERR_CONNECT_FAILED = 2;
    const ERR_QUERY_FAILED = 3;

    protected $outsideMessage;

    public function __construct($message = null, $code = null, $previous = null, $outsideMessage = null) {
        parent::__construct($message, $code, $previous);
        $this->outsideMessage = $outsideMessage;
    }

}
