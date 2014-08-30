<?php
/**
 * User: yiqing
 * Date: 14-8-26
 * Time: 上午11:29
 */

namespace Api\Service;


use Api\Base\Service;

class UserService extends Service {

    public function verbs()
    {
        return array(
            // create 方法 通过post 和get可以访问
          'create'=>array('Post','Get'),
        );
    }
    /**
     * 返回用户名称
     *
     * @param string $name 获取名称
     * @return string
     */
    public function getName($name='')
    {

       print_r(array(
          'method'=>__METHOD__,
           'params'=>func_get_args(),
       ));
    }

    public function helloTo($whom='thinkphp'){
        return array(
            'method'=>__METHOD__,
            'args'=>func_get_args(),
        );
    }

    /**
     *
     * @param string $name
     * @param string $password
     */
    public function create($name,$password)
    {
        return func_get_args() ;
    }
} 