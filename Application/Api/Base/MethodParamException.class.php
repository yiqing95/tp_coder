<?php
/**
 * User: yiqing
 * Date: 14-8-27
 * Time: 下午2:58
 */

namespace Api\Base;


class MethodParamException extends ApiClientException{

    public function __construct( $message = null, $code = 0, \Exception $previous = null)
    {
        // $this->errorCode = 10001;
        parent::__construct(20002, $message, $code, $previous);
    }
} 