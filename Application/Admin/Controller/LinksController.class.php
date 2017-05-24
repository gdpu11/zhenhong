<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台配置控制器
 * @author yangweijie <yangweijiester@gmail.com>
 */
class LinksController extends AdminController {

    /**
     * 后台菜单首页
     * @return none
     */
    public function index(){
        //$this->getMenu();
        $list = M('Links')->order('sort desc,id desc')->select();
        int_to_string($list,array('hide'=>array(1=>'是',0=>'否')));
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);

        $this->meta_title = '友情链接';
        $this->assign('list', $list);
        $this->display();
    }

    public function add($id= 0)
    {
        //$this->getMenu();
        if (IS_POST) {
            $links = D('Links');
            $data = $links->create();
            if(empty($data['id'])){
                $status = $links->add();
            }else{
                $status = $links->save(); //更新基础内容
            }

            if($status){
                $this->success('成功', U('index'));
            }else{
                $this->error('失败');
            }

        }else{

            $info = M('Links')->find($id);
        }

        $this->meta_title = '友情链接管理';
        $this->assign('info', $info);
        $this->display('edit');
    }

    public  function  del($id = 0){
        $ids = I('param.ids');
        if(is_array($ids)) {
            $id = arr2str($ids);
        }
        $links = D('Links');
        $status = $links->delete($id);
        if($status){
            $this->success('删除成功', U('index'));
        }else{
            $this->error('删除失败');
        }
    }

    public  function  getMenu(){
        $User = A('Article');
        $User->getMenu();
    }

}