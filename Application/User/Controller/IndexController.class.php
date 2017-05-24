<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xiaobing360.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 敬水仙 <462766033@qq.com> <http://www.8jie8.com>
// +----------------------------------------------------------------------

namespace User\Controller;
use Think\Controller;

class IndexController extends Controller{
    //安装首页
    public function index(){

    }

    //安装完成
    public function login(){
        $this->display();
    }
}
