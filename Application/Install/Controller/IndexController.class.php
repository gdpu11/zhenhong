<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Install\Controller;
use Think\Controller;
use Think\Storage;

class IndexController extends Controller{
    //安装首页
    public function index(){
        session('update',false); //关闭更新
        if(is_file( LONG_WEB_SITE_LOCK )){
            // 已经安装过了 执行更新程序
            //session('update',true); 开启更新
            $msg = '请删除install.lock文件后再运行升级!';
        }else{
            $msg = '已经成功安装了，请不要重复安装!';
        }
        if(Storage::has(LONG_WEB_SITE_LOCK)){
            $this->error($msg,'/');
        }
        $this->display();
    }

    //安装完成
    public function complete(){
        $step = session('step');

        if(!$step){
            $this->redirect('index');
        } elseif($step != 3) {
            $this->redirect("Install/step{$step}");
        }

        // 写入安装锁定文件
        Storage::put(LONG_WEB_SITE_LOCK, 'lock');
        if(!session('update')){
            //创建配置文件
            $this->assign('info',session('config_file'));
        }
        session('step', null);
        session('error', null);
        session('update',null);
        $this->display();
    }
}
