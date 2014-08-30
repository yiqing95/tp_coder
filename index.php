<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',True);

// 定义应用目录
// define('APP_PATH','./Application/');

//-----------------------------------------------------------------------------------------------\\
// 下面是生成模块 或者控制器 类似yii-gii ror-scaffolding 技术 生完module后加入到可访问module列表中
// 绑定Admin模块到当前入口文件
// define('BIND_MODULE','Api');
// define('BUILD_CONTROLLER_LIST','Index,User,Menu');
// define('BUILD_CONTROLLER_LIST','V1');
//-----------------------------------------------------------------------------------------------//

define('APP_PATH','./Application/');

// 引入composer
require(__DIR__ . '/vendor/autoload.php');
// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单