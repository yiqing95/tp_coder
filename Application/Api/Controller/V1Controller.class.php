<?php
namespace Api\Controller;

use Api\Base\ApiClientException;
use Think\Controller;
use Think\Exception;

class V1Controller extends Controller
{

    public function run()
    {
        echo '执行' . CONTROLLER_NAME . '控制器的' . ACTION_NAME . '操作';
    }

    public function index()
    {

        print_r($_REQUEST);
        $mok = array(
            'method' => 'user.helloTo',
            'params' => array(
                'whom' => 'hi-yiqing ok!',
            ),
        );
        $requestMethodParams = $mok['params'];
        // 解析方法
        $method = $mok['method'];

        if(count($_REQUEST)>1){
            if(isset($_REQUEST['method'])){
                $method = $_REQUEST['method'] ;
            }
            if(isset($_REQUEST['params'])){
                $requestMethodParams = $_REQUEST['params'] ;
            }
        }else{
            print_r($_REQUEST);
        }

        $parts = explode('.', $method);
        if (count($parts) == 2) {
            $serviceName = $parts[0];
            $methodName = $parts[1];
            // 注意service层的类名惯例  XxxService.class.php
            $serviceObj = A(ucfirst($serviceName), 'Service');
        } else {
            // 比较长的方法串暂时不支持
        }

        if ($serviceObj == false) {
            // 服务类名解析出错 证明类初次加载不成功 可以尝试其他策略 或者抛给客户端异常调用
            throw new Exception(printf('不存在服务%s', $serviceName));
        }

        // 利用反射 来调用服务类的某个方法
        if (!method_exists($serviceObj, $methodName)) {
            throw new Exception(printf('不存在方法%s', $methodName));
        }
        $reflectionMethod = new \ReflectionMethod($serviceObj, $methodName);
        // print_r($rm->getParameters());
        $args = $requestMethodParams;

        $pass = array();
        $missing = array();
        foreach ($reflectionMethod->getParameters() as $param) {
            // print_r($param);
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
        if (!empty($missing)) {
            throw new Exception(sprintf('missing required parameters: %s ', implode(', ', $missing)));
        }

        try {
            $rtn = $reflectionMethod->invokeArgs($serviceObj, $pass);
        } catch (\Exception $e) {
            $rtn = array(
                'error' => 1,
                'msg' => $e->getMessage(),
            );
        }
        var_dump(
            array(
                'return : ' => $rtn,
            )
        );
        /*
        print_r($parts);
        echo current($parts);
        echo current($parts);
        */
        /*
         print_r($_REQUEST);
         print_r(U('test/hi',array('p1'=>'v1')));
         print_r(U('Test/test/hi',array('p1'=>'v1')));
         //  $this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;font-size:24px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>欢迎使用 <b>ThinkPHP</b>！</p><br/>[ 您现在访问的是Api模块的V1控制器 ]</div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>','utf-8');
         // 大小写敏感啊 需要首词大写形成标准类名。
         $controller = A('test/MyApi', 'Service');

         $controller = A('Test/MyApi', 'Service');
         var_dump($controller);

         $controller = A('User','Service');
         print_r($controller);
         printf('本模块下的服务: %s ;',get_class($controller));


        */
    }

    public function go()
    {
        $runner = new \Api\Base\Runner();
        /*
        $_GET = $_GET + array(
                // 'method'=>'test.user.helloTo',
                'method'=>'user.helloTo',
                'params'=>array(
                    'p1'=>'value1',
                ),
            );
        $runner->setRequestParams($_GET);
        */
        try{
            $result =  $runner->run() ;

            // 得到结果后按照格式进行format 现在只支持基本的json即可
            $this->ajaxReturn(
              array(
                  'code'=>1,
                   'result'=>$result,
              )
            );

        }
        catch (ApiClientException $ex){
            // print_r($ex);
            $this->ajaxReturn(array(
                    'code'=>$ex->errorCode,
                    'message'=>$ex->getMessage(),
                ),
                'json'
            );

        }
        catch (\Exception $ex){
            // print_r($ex);
            $this->ajaxReturn(array(
                    'code'=>$ex->getCode(),
                    'message'=>$ex->getMessage(),
                ),
                'json'
            );
        }


    }

    public function goTest()
    {
        $_GET = $_GET + array(
                // 'method'=>'test.user.helloTo',
                'method'=>'user.helloTo',
                'params'=>array(
                  'p1'=>'value1',
                ),
            );

        $runner = new \Api\Base\Runner();
        $runner->setRequestParams($_GET);
        try{
            $runner->run() ;

            // 测试用户创建
            $req = array(
                'method'=>'batch',
                'params'=>array('qing','qing'),
            );
            $runner->setRequestParams($req);
            print_r( $runner->run() );



            // 测试用户创建
            $req = array(
                'method'=>'user.create',
                'params'=>array('qing','qing'),
            );
            $runner->setRequestParams($req);
            $runner->run() ;


            $req = array(
                'method'=>'test.myApi.helloTo',
                'params'=>array(),
            );
            $runner->setRequestParams($req);
            $runner->run() ;


        }
        catch (ApiClientException $ex){
            // print_r($ex);
            $this->ajaxReturn(array(
                    'code'=>$ex->errorCode,
                    'message'=>$ex->getMessage(),
                ),
                'json'
            );

        }
        catch (\Exception $ex){
           // print_r($ex);
           $this->ajaxReturn(array(
               'code'=>$ex->getCode(),
                   'message'=>$ex->getMessage(),
           ),
           'json'
           );
        }


    }


    public function testComposer(){
        $user = \ORM::for_table('user')
            ->where_equal('username', 'j4mie')
            ->find_one();
    }

}