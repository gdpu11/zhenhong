<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class HomeController extends Controller {

	/* 空操作，用于输出404页面 */
	public function _empty($error=""){
		//$this->redirect('Index/index');
        header("HTTP/1.0 404 Not Found");
		$this->assign('error',$error);
        $this->display('Public/404');
	}

    public function sitemap(){
        /* 获取当前分类列表 */
        $Document = D('Document');
        $where['status'] = 1;
        $list = $Document->page(1, 500)->select();
        //By.jingshuixian
        $list = forma_list_data($list,'arc'); //格式化数据

        $this->assign('list',$list);
        //header('Content-type: text/xml');
        $this->display('Public/sitemap');
        $this->buildHtml('','sitemap.xml','Public/sitemap');
    }

    protected function _initialize(){
        // By.jingshuixian  采用行为扩展的方式  放到  \Application\Common\Behavior\InitHookBehavior.class.php
        /* 读取站点配置 */
        //$config = api('Config/lists');
        //C($config); //添加配置
        // By.jingshuixian  前台模板主题
        //define('__YQ_TMPL__',C('WEB_DEFAULT_THEME'));
        // By.jingshuixian end

        if(!C('WEB_SITE_CLOSE')){
            $this->error('站点已经关闭，请稍后访问~');
        }
    }

	/* 用户登录检测 */
	protected function login(){
		/* 用户登录检测 */
		is_login() || $this->error('您还没有登录，请先登录！', U('User/login'));
	}

}
