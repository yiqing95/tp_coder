<?php
/**
 * User: yiqing
 * Date: 14-8-27
 * Time: 下午2:33
 */

namespace Api\Base;


class ApiClientException extends \Exception{

    /**
     * 异常编码
     * @var int
     */
    public $errorCode = 2000 ;

    /**
     * Constructor.
     * @param string $errorCode
     * @param string $message error message
     * @param integer $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     * @internal param int $errorCode , such as 10000,20000, etc.
     */
    public function __construct($errorCode, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->errorCode = $errorCode;
        parent::__construct($message, $code, $previous);
    }

} 