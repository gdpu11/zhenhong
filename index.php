<?php
// +----------------------------------------------------------------------
// | Xiaobing360 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://zhenhong.liiking.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: 佛山市振鸿集团 <1923510186@qq.com> <http://zhenhong.liiking.com>
// +----------------------------------------------------------------------

/**
 * Longbing
 */
require('./ThinkPHP/long.php');
/**
 * 系统调试设置
 * 项目正式部署后请设置为 false
 */
define('APP_DEBUG', true );
/**
 * 超级缓存
 * 兼容日志记录和加快访问速度
 */
define('RUNTIME_ALLINONE', false);

define('BIND_MODULE','Home');

/**
 * 引入核心入口
 * ThinkPHP亦可移动到WEB以外的目录
 */
require './ThinkPHP/ThinkPHP.php';