<?php
/**
 * User: yiqing
 * Date: 14-8-27
 * Time: 上午11:57
 */

namespace Api\Base;


class Runner
{


    /**
     * @var string
     */
    protected $requestMethod = 'GET';
    /**
     *
     * @var array
     */
    protected $requestParams = array();


    public function  __construct()
    {
        $this->init();
    }

    protected function init()
    {
        // ----------------------------------------------------------------\\
        #  判断当前请求的方法类型

        $requestMethodAsserts = array(
            'IS_GET',
            'IS_POST',
            'IS_PUT',
            'IS_DELETE',
        );
        // TODO 注意可能有不是上述方法的其他请求方法存在 比如patch options
        foreach ($requestMethodAsserts as $assert) {
            if (constant($assert) == true) {
                $this->requestMethod = substr($assert, 3);
            }
        }
        // ----------------------------------------------------------------//
        // 注册错误处理器跟异常处理器
        // set_error_handler(array($this, 'errorHandler'), E_ERROR);
        // set_exception_handler(array($this, 'exceptionHandler'));
    }

    /**
     * 设置请求参数  可以模拟请求
     *
     * @param array $params
     */
    public function setRequestParams($params = array())
    {
        $this->requestParams = $params;
    }

    /**
     * 返回请求参数
     * @return array
     */
    public function getRequestParams()
    {

        if (empty($this->requestParams)) {
            // FIXME is this equal to $_REQUEST
            $this->requestParams = $_GET + $_POST;
            // print_r($_POST);
        }
        return $this->requestParams;
    }

    protected function prepare()
    {

    }


    protected $apiHandlerMap = array(
        'xxxxxxxxxxx.xxxx.xxxx' => 'XXXModule.XxxxxxxService.method',
    );

    /**
     * @param array $array
     * @return bool
     */
    public static function isAssociative(array &$array)
    {
//        foreach(array_keys($a) as $key)
//            if (!is_int($key)) return TRUE;
//        return FALSE;
        reset($array);
        $k = key($array);
        return !(is_int($k) || is_long($k));
    }

    /**
     * 根据请求的api方法串 跟api方法参数 创建service及其可被执行的参数
     *
     * @param string $apiMethod
     * @param array $apiParams
     * @throws MethodNameException
     * @throws MethodParamException
     * @return array|bool a callable array [callbale,params]
     */
    protected function createApiHandler($apiMethod , $apiParams = array())
    {

        if(empty($apiMethod)){
            // 服务类名没有传递
            throw new MethodNameException(sprintf('empty method !'));
        }

        //  系统处理 批量处理最好参考jsonRpc2.x规范中的批处理方式
        if ($apiMethod == 'batch') {
            // 批处理 小心递归现象啊！！！
            // 参数传递格式，{method:batchRun, params: [{},{},...]
            return array(
                array($this, 'batchRun'),
                $apiParams,
            );
        }

        //TODO 这个允许自己进行内部api处理映射 可以不遵从惯例寻址
        if (isset($this->apiHandlerMap[$apiMethod])) {

        }

        // 注意api方法串可能出现的情形 惯例是用"."点号分隔--参考淘宝那种
        $parts = explode('.', $apiMethod);
        $methodName = array_pop($parts);
        // 这个可能是当前模块下 或者是其他模块下的服务名
        $serviceName = implode('/', array_map('ucfirst', $parts));
        /*
        print_r(array(
           'methodName'=>$methodName,
            'serviceName'=>$serviceName ,
        ));
        die(__METHOD__) ;
        */
        // 注意service层的类名惯例  XxxService.class.php
        $serviceObj = A(ucfirst($serviceName), 'Service');

        if ($serviceObj == false) {
            // 服务类名解析出错 证明类初次加载不成功 可以尝试其他策略 或者抛给客户端异常调用
            throw new MethodNameException(sprintf('no such service%s', $serviceName));
        }

        // 利用反射 来调用服务类的某个方法
        if (!method_exists($serviceObj, $methodName)) {
            throw new MethodNameException(sprintf('no such method %s', $methodName));
        }
        $reflectionMethod = new \ReflectionMethod($serviceObj, $methodName);
        // print_r($rm->getParameters());
        $args = $apiParams;

        $pass = array();
        $missing = array();
        if (self::isAssociative($args)) {
            foreach ($reflectionMethod->getParameters() as $param) {
                // print('the param: '.$param->getName().' <br/>');
                /* @var $param ReflectionParameter */
                if (isset($args[$param->getName()])) {
                    $pass[] = $args[$param->getName()];
                } elseif ($param->isDefaultValueAvailable()) {
                    $pass[] = $param->getDefaultValue();
                } else {
                    $missing[] = $param->getName();
                }
            }
        } // 非关联数组形式传递的参数
        else {
            $pass = $args;
            // 传递的参数 比方法参数少 那么需要检查是不是有遗漏
            // if(($pass_arg_count = count($pass)) < ($method_param_count = count($reflectionMethod->getParameters()))){
            if (($pass_arg_count = count($pass)) < ($num_required = $reflectionMethod->getNumberOfRequiredParameters())) {
                $allMethodParams = $reflectionMethod->getParameters();

                $missingParams = array_slice($allMethodParams, $pass_arg_count, $num_required - $pass_arg_count);
                foreach ($missingParams as $param) {
                    // print('the param: '.$param->getName().' <br/>');
                    /* @var $param ReflectionParameter */
                    $missing[] = $param->getName();
                }
            }
        }

        if (!empty($missing)) {
            throw new MethodParamException(sprintf('missing required parameters: %s ', implode(', ', $missing)));
        }

        return array(
            array($serviceObj, $methodName),
            $pass
        );

    }

    public function run()
    {
        $requestParams = $this->getRequestParams();
        // print_r($requestParams);
        $apiMethod = $requestParams['method'];
        $apiMethodParams = isset($requestParams['params']) ? $requestParams['params'] : array();

        list($handler, $params) = $this->createApiHandler($apiMethod, $apiMethodParams);

        //--------------------------------------------------------------------------------------------------\\
        /**
         * 本段进行服务方法的动词验证
         */
        $handlerObj = current($handler);
        $handlerMethodName = end($handler);
        if (method_exists($handlerObj, 'verbs') || $handlerObj instanceof Service) {
            // FixMe 这里用的抽象类 如果用接口标识是否更好些？
            $methodVerbMap = call_user_func(array($handlerObj, 'verbs'));
            // $handlerObj->verbs();
            // 服务类声明了方法保护
            if (!empty($methodVerbMap)) {
                if (isset($methodVerbMap[$handlerMethodName])) {
                    $methodVerbs = $methodVerbMap[$handlerMethodName] ;
                    if(is_string($methodVerbs)){
                        $methodVerbs = array($methodVerbs);
                    }
                    $allowed = array_map('strtoupper', $methodVerbs);
                    if (!in_array($this->requestMethod, $allowed)) {
                        // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.7
                        // Response()->getHeaders()->set('Allow', implode(', ', $allowed));
                        throw new MethodNotAllowedException('Method Not Allowed. This url can only handle the following request methods: ' . implode(', ', $allowed) . '.');
                    }
                }
            }
        }
        //--------------------------------------------------------------------------------------------------//
        /*
        var_dump(array(
            $handler,$params,
        ));
        die(__METHOD__);
        */
        $result = call_user_func_array($handler, $params);
        // print_r($result);
        return $result;

    }


    /**
     * @return array
     */
    public function batchRun()
    {
        $batchCallParams = func_get_args();
        // 接下来遍历调用每个方法
        return array(
            'method' => __METHOD__,
            'params' => $batchCallParams,
        );
        $results = array();
        foreach ($batchCallParams as $callParams) {
            $this->setRequestParams($callParams);
            $results[] = $this->run();
        }
        return $results;
    }


    //-----------------------------------------------------------------------------------\\
    ###  错误异常处理 两个php回调函数

    public function errorHandler($errno, $message, $file, $line)
    {

        $data = array(
            'error' => 1,
            'errno' => $errno,
            'message' => $message,
            'line' => $line,
            'file' => $file,
        );
        echo self::outputJson($data);
        exit(0);
    }

    public function exceptionHandler($e)
    {
        $data = array(
            'error' => 1,
            'errno' => $e->getCode(),
            'message' => $e->getMessage(),
        );
        exit(0);
    }

    protected function append_error_handler($handler)
    {
        $this->set_error_handlers(array(set_error_handler($handler), $handler));
    }

    protected function prepend_error_handler($handler)
    {
        $this->set_error_handlers(array($handler, set_error_handler($handler)));
    }

    protected function set_error_handlers($handlers)
    {
        $handlers = (is_array($handlers) ? $handlers : array($handlers));
        set_error_handler(function ($level, $message, $file = null, $line = null, $context = null) use ($handlers) {
            foreach ($handlers as $handler) {
                if ($handler) {
                    call_user_func($handler, $level, $message, $file, $line, $context);
                }
            }
        });
    }

    //-----------------------------------------------------------------------------------//
}