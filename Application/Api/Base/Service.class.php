<?php
/**
 * User: yiqing
 * Date: 14-8-27
 * Time: 上午11:57
 */

namespace Api\Base;


abstract class Service {

    /**
     * 声明那些方法可以使用什么类型的http方法（动词）来访问 如：
     *   return [
        'index' => ['GET', 'HEAD'],
        'view' => ['GET', 'HEAD'],
        'create' => ['POST'],
        'update' => ['PUT', 'PATCH'],
        'delete' => ['DELETE'],
        ];
     *
     * Declares the allowed HTTP verbs.
     * Please refer to [[VerbFilter::actions]] on how to declare the allowed verbs.
     * @return array the allowed HTTP verbs.
     */
    public  function verbs()
    {
        return array();
    }


} 