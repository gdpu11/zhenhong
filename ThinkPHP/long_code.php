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
 * 获取目录的结构
 * @author 李俊
 * @param  [string] $path [目录路径]
 * @return [array]       [目录结构数组]
 */
function dirtree__($path) {
    $handle = opendir($path);
    $itemArray=array();
    while (false !== ($file = readdir($handle))) {
        if (($file=='.')||($file=='..')){
        }elseif (is_dir($path.$file)) {
            try {
                $dirtmparr=dirtree($path.$file.'/');
            } catch (Exception $e) {
                $dirtmparr=null;
            };
            $itemArray[$file]=$dirtmparr;
        }else{
            array_push($itemArray, $file);
        }
    }
    return $itemArray;
}

$data = "xiaobing009";

$dir___ = dirtree__('./Websites');
if(empty($dir___)){
	echo "错误: Websites 目录下没有站点文件!";
	exit;
}
$data = $dir___[0];

/*设置全局变量*/
define('LONG_VERSION', 20161123);
define('LONG_WEB_SITE_NAME', $data);
define("LONG_WEBSITES",'./Websites/');
define('LONG_WEB_SITE_PATH', LONG_WEBSITES.$data );
define('LONG_WEB_SITE_DATA_PATH', LONG_WEB_SITE_PATH.'/Data' );
define('LONG_WEB_SITE_TMPL_PATH', LONG_WEB_SITE_PATH.'/Templates' );
/*前台相对模板目录*/
define('LONG_WEB_SITE_TMPL_PATH_STATIC', '/Websites/'.$data.'/Templates' );
define('LONG_WEB_SITE_STATIC', '/Websites/'.$data );

define('LONG_WEB_SITE_PATH_UPLOAD', LONG_WEBSITES.$data );
define ('LONG_WEB_SITE_LOCK', LONG_WEB_SITE_PATH.'/install.lock' );

///////////////////////////////////////////////////////////////以下是公用配置//////////////////
/**
 * 应用目录设置
 * 安全期间，建议安装调试完成后移动到非WEB目录
 */
define ( 'APP_PATH', './Application/' );

if(!is_file(LONG_WEB_SITE_LOCK)){
    /*获取网页地址*/
     $url_self = strtolower($_SERVER['PHP_SELF']);
     if($url_self != '/install.php')
     {
         header('Location: ./install.php?state=install');
         exit;
     }
 }
 /*设置工作站点*/
define('APP_STATUS',"config_".LONG_WEB_SITE_NAME);
/**
 * 缓存目录设置
 * 此目录必须可写，建议移动到非WEB目录
 */
define ( 'RUNTIME_PATH', './Runtime/'.$data."/" );

