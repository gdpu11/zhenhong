<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Common\Api;
use JPush\Model as M;
use JPush\JPushClient;

class MessagesController extends HomeController {

    const VERIFY_TYPE = 12;

    public function add($verify=''){

        if (IS_POST) {
            /* 检测验证码 */
            if(!check_verify($verify,VERIFY_TYPE)){
                //$this->error('验证码输入错误！');
            }

            $Model  =   D(parse_name(get_table_name(5),1));
            // 获取模型的字段信息
            $Model  =   $this->checkAttr($Model,5);

            $data =  $Model->create() && $Model->add();

            if($data){
                //@sendWX(I("name"),"访客电话为: " . I("phone"));
				
				hook('messageSaveComplete', array('name'=>I('name'),'tel'=>I('phone'),'content'=>'content'));
				
                $this->success('留言成功!');
            }else{
                $this->error('留言失败!');
            }
        }

    }

    protected function checkAttr($Model,$model_id){
        $fields     =   get_model_attribute($model_id,false);
        $validate   =   $auto   =   array();
        foreach($fields as $key=>$attr){
            if($attr['is_must']){// 必填字段
                $validate[]  =  array($attr['name'],'require',$attr['title'].'必须!');
            }
            // 自动验证规则
            if(!empty($attr['validate_rule'])) {
                $validate[]  =  array($attr['name'],$attr['validate_rule'],$attr['error_info']?$attr['error_info']:$attr['title'].'验证错误',0,$attr['validate_type'],$attr['validate_time']);
            }
            // 自动完成规则
            if(!empty($attr['auto_rule'])) {
                $auto[]  =  array($attr['name'],$attr['auto_rule'],$attr['auto_time'],$attr['auto_type']);
            }elseif('checkbox'==$attr['type']){ // 多选型
                $auto[] =   array($attr['name'],'arr2str',3,'function');
            }elseif('date' == $attr['type']){ // 日期型
                $auto[] =   array($attr['name'],'strtotime',3,'function');
            }elseif('datetime' == $attr['type']){ // 时间型
                $auto[] =   array($attr['name'],'strtotime',3,'function');
            }
        }
        return $Model->validate($validate)->auto($auto);
    }

	public function addNo($verify=''){

        if (IS_POST) {


            $message = D('Messages');
            $data =  $message->create();

            if($data && $message->add()){
                //@sendWX(I("name"),"访客电话为: " . I("phone"));
				
				hook('messageSaveComplete', array('name'=>I('name'),'tel'=>I('phone'),'content'=>'content'));
				
                $this->success('留言成功!');
            }else{
                $this->error('留言失败!');
            }
        }

    }


    /* 验证码，用于登录和注册 */
    public function verify(){
        $verify = new \Think\Verify();
        $verify->codeSet = '123456789';
        $verify->length = 4;
        $verify->useCurve = false;
        $verify->entry(VERIFY_TYPE);
    }

    /**
     * 统计留言数量
     */
    public function tongji(){
        $map['status'] = 1;
        echo(D('Messages')->where($map)->count());
    }
}
