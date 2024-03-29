<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台频道控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */

class ChannelController extends AdminController {

    /**
     * 频道列表
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        $pid = I('get.pid', 0);
        /* 获取频道列表 */
        $map  = array('status' => array('gt', -1), 'pid'=>$pid);
        $list = M('Channel')->where($map)->order('sort asc,id asc')->select();

        $pid = I('get.pid', 0);
        //获取父导航
        if(!empty($pid)){
            $parent = M('Channel')->where(array('id'=>$pid))->field('title')->find();
            $this->assign('parent', $parent);
        }


        $this->assign('list', $list);
        $this->assign('pid', $pid);
        $this->meta_title = '导航管理';
        $this->display();
    }

    /**
     * 添加频道
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function add(){
        if(IS_POST){
            $Channel = D('Channel');
            $data = $Channel->create();
            if($data){
                $id = $Channel->add();
                if($id){

                    //By. jingshuixian 2015年5月29日12:09:02   如果sort为 0  设置默认排序值为 id
                    $sort = I('post.sort', 0);
                    if($sort == 0){
                        $data = array();
                        $data['id'] = $id;
                        $data['sort'] = $id;
                        $Channel->save($data);
                    }

                    //$this->success('新增成功', U('index'));
                    //记录行为
                    action_log('update_channel', 'channel', $id, UID);

                    //jingshuixian 2015年5月29日12:09:02  跳转到编辑的栏目页
                    $pid = I('post.pid', 0);
                    $this->success('新增成功', U('Channel/index?pid='.$pid));
                    //jingshuixian end

                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($Channel->getError());
            }
        } else {
            $pid = I('get.pid', 0);
            //获取父导航
            if(!empty($pid)){
                $parent = M('Channel')->where(array('id'=>$pid))->field('title')->find();
                $this->assign('parent', $parent);
            }

            //By.jingshuixian  获取分类信息
            $tree =  get_child_category(0,4,0);
            //var_dump($list);

            $this->assign('pid', $pid);
            $this->assign('info',null);
            $this->assign('tree',$tree);
            $this->meta_title = '新增导航';
            $this->display('edit');
        }
    }

    /**
     * 编辑频道
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function edit($id = 0){
        if(IS_POST){
            $Channel = D('Channel');
            $data = $Channel->create();
            if($data){
                if($Channel->save()){
                    //记录行为
                    action_log('update_channel', 'channel', $data['id'], UID);
                    //$this->success('编辑成功', U('index'));
                    //By. jingshuixian 2015年5月29日12:09:02  跳转到编辑的栏目页
                    $pid = I('post.pid', 0);
                    $this->success('新增成功', U('Channel/index?pid='.$pid));
                    //By. jingshuixian end

                } else {
                    $this->error('编辑失败');
                }

            } else {
                $this->error($Channel->getError());
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = M('Channel')->find($id);

            if(false === $info){
                $this->error('获取配置信息错误');
            }

            $pid = I('get.pid', 0);
            //获取父导航
            if(!empty($pid)){
            	$parent = M('Channel')->where(array('id'=>$pid))->field('title')->find();
            	$this->assign('parent', $parent);
            }

            //By.jingshuixian  获取分类信息
            $tree =  get_child_category(0,4,0);
            //var_dump($list);


            $this->assign('pid', $pid);
            $this->assign('info', $info);
            $this->assign('tree', $tree);
            $this->meta_title = '编辑导航';
            $this->display();
        }
    }

    /**
     * 删除频道
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function del(){
        $id = array_unique((array)I('id',0));

        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('id' => array('in', $id) );
        if(M('Channel')->where($map)->delete()){
            //记录行为
            action_log('update_channel', 'channel', $id, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 导航排序
     * @author huajie <banhuajie@163.com>
     */
    public function sort(){
        if(IS_GET){
            $ids = I('get.ids');
            $pid = I('get.pid');

            //获取排序的数据
            $map = array('status'=>array('gt',-1));
            if(!empty($ids)){
                $map['id'] = array('in',$ids);
            }else{
                if($pid !== ''){
                    $map['pid'] = $pid;
                }
            }
            $list = M('Channel')->where($map)->field('id,title')->order('sort asc,id asc')->select();

            $this->assign('list', $list);
            $this->meta_title = '导航排序';
            $this->display();
        }elseif (IS_POST){
            $ids = I('post.ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key=>$value){
                $res = M('Channel')->where(array('id'=>$value))->setField('sort', $key+1);
            }
            if($res !== false){
                $this->success('排序成功！');
            }else{
                $this->error('排序失败！');
            }
        }else{
            $this->error('非法请求！');
        }
    }

    public function tree($tree = null){
        //C('_SYS_GET_CATEGORY_TREE_') || $this->_empty();
        $this->assign('tree', $tree);
        $this->display('tree');
    }

}