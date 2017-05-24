<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2013 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: By.jingshixian
// +----------------------------------------------------------------------
namespace Common\Behavior;
use Think\Behavior;
use Think\Hook;
defined('THINK_PATH') or exit();

//优先加载后台配置值
class LoadUserConfigBehavior extends Behavior {

    // 行为扩展的执行入口必须是run
    public function run(&$content){
        if(defined('BIND_MODULE') && BIND_MODULE === 'Install') return;
            /* 读取站点配置 */
            $config = api('Config/lists');
            C($config); //添加配置

            //动态设置URL模式
            $url_model = C('XIAOBING360_URL_MODEL');
            if($url_model == 2){  //兼容模式
                define('__YQ_URL_MODEL__',3);
                define('LONG_IIS6', 1);
            }else{  // 默认精简模式
                define('__YQ_URL_MODEL__',2);
                define('LONG_IIS6', 0);
            }

            $host = strtolower($_SERVER['HTTP_HOST']);
            $web_m_closed = C('WEB_M_CLOSED'); //手机站状态
            $web_m_url =  strtolower(C('WEB_M_URL'));
            if( $web_m_closed ){  //开启手机站
                //自动跳转手机站
                if( BIND_MODULE == 'Home' && !empty($web_m_url) && $host != $web_m_url && $this->is_mobile()){
                    $self_url = $this->curPageURL();
                    $redirect_url = str_replace($host,$web_m_url ,$self_url);
                    redirect($redirect_url);
                }
            }

            //判断模板
            if($web_m_closed && ( $host == $web_m_url || $this->is_mobile())){  //是手机站站域名  或 手机站域名为空  就自动加载手机站模板
                define('__YQ_TMPL__',C('WEB_M_TMPL'));
            }else{
                define('__YQ_TMPL__',C('WEB_DEFAULT_THEME'));
            }

        //加载OME配置文件信息
        $oem_path =LONG_WEB_SITE_PATH .'/config.php' ;
        if(file_exists($oem_path)) {
            $ome = include_once($oem_path);
            C($ome);
        }
    }


    private  function is_mobile() {

    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        return true;

    //此条摘自TPM智能切换模板引擎，适合TPM开发
    if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT'])
        return true;
    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
        //找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
    //判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
        );
        //从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    //协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

    /**
     * 获取完整url
     * @return string
     */
    private function curPageURL()
    {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;

    }

}