<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use OT\DataDictionary;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends HomeController {

	//系统首页
    public function index(){

        //By.jingshuixian 首页SEO信息 begin
        $title = C('WEB_SITE_TITLE_SEO');
        $description =C('WEB_SITE_DESCRIPTION');;
        $keywords = C('WEB_SITE_KEYWORD');;

        if(empty($title)){
            $title = C('WEB_SITE_TITLE');
        }
        $title = format_config($title);
        $description = format_config($description);
        $keywords = format_config($keywords);
        //By.jingshuixian 首页SEO信息 end

        $this->assign('title',$title);
        $this->assign('description', $description);
        $this->assign('keywords', $keywords);
        if(C('IS_WELCOME')){
            $this->display("Html/welcome");
        }else{
            $this->display("Html/index");
        }

    }
    //第二首页
    public function home(){
        $this->display("Html/index");
    }

}