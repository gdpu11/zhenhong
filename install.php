<?php
// +----------------------------------------------------------------------
// | Xiaobing360 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://zhenhong.liiking.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: 佛山市振鸿集团 <1923510186@qq.com> <http://zhenhong.liiking.com>
// +----------------------------------------------------------------------


if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
/**
 * Longbing
 */
require('./ThinkPHP/long.php');

/**
 * 系统调试设置
 * 项目正式部署后请设置为false
 */
define ( 'APP_DEBUG', true );
define ( 'BIND_MODULE','Install');

/**
 * 引入核心入口
 * ThinkPHP亦可移动到WEB以外的目录
 */
require './ThinkPHP/ThinkPHP.php';