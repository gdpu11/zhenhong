<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use User\Api\UserApi;

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class ApiController extends HomeController {

	/* 用户中心首页 */
	public function index(){
		
	}

    public function lists($category=0,$p){
        $category = $this->category();
        /* 获取当前分类列表 */
        $Document = D('Home/Document');
        $list = $Document->page($p, $category['list_row'])->lists($category['id']);
        if(false === $list){
            $status = 0;
            $message='获取列表数据失败！';
        }else{
            $status = 1;
            $message='获取列表数据成功！';
        }


        $list = forma_list_data($list,'arc'); //格式化数据
        if(empty($list)) $status = 2 ;
        $data['info']   =   $message;
        $data['status'] =   $status;
        $data['lists']=$list;
        //echo '{"a" : "abc"}';
        //echo 11;
        $this->ajaxReturn($data,'JSON');
    }
    /* 文档分类检测 */
    private function category($id = 0){
        /* 标识正确性检测 */
        $id = $id ? $id : I('get.category', 0);
        if(empty($id)){
            $this->error('没有指定文档分类！');
        }

        /* 获取分类信息 */
        $category = D('Home/Category')->info($id);
        if($category && 1 == $category['status']){
            switch ($category['display']) {
                case 0:
                    $this->error('该分类禁止显示！');
                    break;
                //TODO: 更多分类显示状态判断
                default:
                    //By.jingshuixian 格式化内容
                    $category = format_item_data($category,'cate');
                    return $category;
            }
        } else {
            $this->error('分类不存在或被禁用！');
        }
    }

    public function detail($id = 0, $p = 1){
        /* 获取详细信息 */
        //By.jingshuixian

        if(is_numeric($id)){ //通过ID查询
            $map['id'] = $id;
        } else { //通过标识查询
            $map['name'] = $id;
        }
        //By.jingshuixian  end
        $Document = D('Home/Document');
        $info = $Document->detail($id);


        if(!$info){
            $status = 0;
            $message='获取详情数据失败！';
        }else{
            $status = 1;
            $message='获取详情数据成功！';
        }
        $newstext = $info['content'];
        $tt1 ="/<[img|IMG].*?src=[\'|\"](\/Uploads.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";//'/(]+src\s*=\s*”?([^>"\s]+)”?[^>]*>)/im'
        $newstext= preg_replace($tt1, '<img src="'. C('WEB_SITE_URL') .'$1" />', $newstext);
        $tt2 ="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpg]))[\'|\"].*?[\/]?>/";
        $newstext= preg_replace($tt2, '<img src="$1" width="100%"/>', $newstext);
        $info['content'] = $newstext;
        //echo $newstext;
        if(empty($info)) $status = 2 ;
        $data['info']   =   $message;
        $data['status'] =   $status;
        $data['document']=$info;
        //echo '{"a" : "abc"}';
        //echo 11;
        $this->ajaxReturn($data,'JSON');
    }

    public function messages($p=1,$pageszie = 1){

        $messages = D('Messages');

        $list = $messages->page($p,$pageszie)->order('id desc')->select();


        if(false === $list){
            $status = 0;
            $message='获取列表数据失败！';
        }else{
            $status = 1;
            $message='获取列表数据成功！';
        }


        //如果 list 为空 状态为2
        if(empty($list))
        {
            $status = 2 ;
            $message='暂无信息！';
        }
        ints_to_string($list,array('list1'=>array(1=>'建设全新的企业网站',2=>'现有网站改版',3=>'界面设计与美化',4=>'网站优化及管理',5=>'APP开发')));
        ints_to_string($list,array('list2'=>array(1=>'对功能需求比较高',2=>'对设计创意要求比较高',3=>'需要可以购物支付',4=>'搜索引擎排名')));
        ints_to_string($list,array('list3'=>array(1=>'3000~6000元',2=>'6千~1万',3=>'1万~2万',4=>'2万~5万',5=>'需要招投标')));


        $data['info']   =   $message;
        $data['status'] =   $status;
        $data['lists']=$list;

        $this->ajaxReturn($data,'JSON');


    }

}
