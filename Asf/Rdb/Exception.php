<?php

/**
 * @author yuanjian
 *
 */
class Asf_Rdb_Exception extends Asf_Exception {
    const ERR_NOT_SUPPORT_HANDLER = 1;
    const ERR_CONNECT_FAILED = 2;
    const ERR_QUERY_FAILED = 3;

    public function __construct($message = null, $code = null,  $innerMessage = null, $previous = null) {
        parent::__construct($message, $code, $innerMessage, $previous);
    }

}
