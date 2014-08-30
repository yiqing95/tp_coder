<?php
/**
 * User: yiqing
 * Date: 14-8-28
 * Time: 上午10:50
 */

namespace Api\Base;


class MethodNotAllowedException extends ApiClientException{

    public function __construct( $message = null, $code = 0, \Exception $previous = null)
    {
        // $this->errorCode = 10001;
        parent::__construct(20004, $message, $code, $previous);
    }
} 