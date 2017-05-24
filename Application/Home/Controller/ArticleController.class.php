<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;

/**
 * 文档模型控制器
 * 文档模型列表和详情
 */
class ArticleController extends HomeController {
    const  TMPL_PATH = 'Html/';
    /* 文档模型频道页 */
	public function index(){
		/* 分类信息 */
		//$category = $this->category();

		//频道页只显示模板，默认不读取任何内容
		//内容可以通过模板标签自行定制


        //By.jingshuixian
        //$id = $category['id'];
        //$pid = $category['pid'];
        //$tid = $category['tid'];
        //$topCategory = $this->category($tid);
		/* 模板赋值并渲染模板 */
        //$this->assign('id', $id);
        //$this->assign('tid', $tid);
        //$this->assign('pid', $pid);
        //$this->assign('category', $category);
        //$this->assign('topCategory', $topCategory);
        //By.jingshuixian end

		//$this->display($category['template_index']);
        return $this->_empty("此功能未开启");
	}

	/* 文档模型列表页 */
	public function lists($p = 1){


        /* 分类信息 */
		$category = $this->category();

        /*获取分类筛选*/
        $mid  =  $category['model'][0];
        $filter = get_filter($category['id'] , $mid);


		/* 获取当前分类列表 */
		$Document = D('Document');
		$list = $Document->page($p, $category['list_row'])->lists($category['id']);
		if(false === $list){
			//$this->error('获取列表数据失败！');
			return $this->_empty("获取列表数据失败！");
		}
        //By.jingshuixian
        $list = forma_list_data($list,'arc'); //格式化数据

        $id = $category['id'];
        $pid = $category['pid'];
        $tid = $category['tid'];
        $topCategory = $this->category($tid);
        $pCategory =  $pid ? $this->category($pid) : null ;
        /* 模板赋值并渲染模板 */
        $this->assign('id', $id);
        $this->assign('cid', $id);
        $this->assign('tid', $tid);
        $this->assign('pid', $pid);
        $this->assign('mid', $mid);
        $this->assign('topCategory', $topCategory);
        $this->assign('pCategory', $pCategory);


        $title = $category['title'];
        $description =$category['description'];
        $keywords = $category['keywords'];

        if(!empty($category['meta_title'])){
            $title = $category['meta_title'];
        }else{
            $title .='-'. C('WEB_SITE_TITLE');
        }
        $addfind =array('%title%');
        $addreplace=array($category['title']);

        $title = format_config($title,$addfind,$addreplace);
        $description = format_config($description,$addfind,$addreplace);
        $keywords = format_config($keywords,$addfind,$addreplace);

        $this->assign('title',$title);
        $this->assign('description', $description);
        $this->assign('keywords', $keywords);

        $this->assign('filter',$filter);

        //2016年11月1日11:19:47
        $tmpl = I('tmpl')?I('tmpl'):$category['template_lists'];

        //By.jingshuixian end

        if(IS_AJAX){
            $list = count($list) ? $list : array();
            $data['lists'] = $list;
            $this->ajaxReturn($data);


        }else{
            /* 模板赋值并渲染模板 */
            $this->assign('category', $category);
            $this->assign('list', $list);
            $this->display(self::TMPL_PATH.$tmpl);
        }



	}

    /**
     * @param int $p
     * @author By.jingshuixian
     */
    public function page($p = 1){
        /* 分类信息 */
        $category = $this->category();

        //By.jingshuixian
        $id = $category['id'];
        $pid = $category['pid'];
        $tid = $category['tid'];
        $topCategory = $this->category($tid);
        $pCategory =  $pid ? $this->category($pid) : null ;
        /* 模板赋值并渲染模板 */
        $this->assign('id', $id);
        $this->assign('cid', $id);
        $this->assign('tid', $tid);
        $this->assign('pid', $pid);
        $this->assign('topCategory', $topCategory);
        $this->assign('pCategory', $pCategory);
        $this->assign('category', $category);
        $this->assign('info', $category);


        $title = $category['title'];
        $description =$category['description'];
        $keywords = $category['keywords'];

        if(!empty($category['meta_title'])){
            $title = $category['meta_title'];
        }else{
            $title .='-'.  C('WEB_SITE_TITLE');
        }

        //$title = format_config($title);
        //$description = format_config($description);
        //$keywords = format_config($keywords);

        $addfind =array('%title%');
        $addreplace=array($category['title']);

        $title = format_config($title,$addfind,$addreplace);
        $description = format_config($description,$addfind,$addreplace);
        $keywords = format_config($keywords,$addfind,$addreplace);

        $tmpl = I('tmpl')?I('tmpl'):$category['template_page'];

        $this->assign('title',$title);
        $this->assign('description', $description);
        $this->assign('keywords', $keywords);

        $this->display(self::TMPL_PATH.$tmpl);
    }



	/* 文档模型详情页 */
	public function detail($id = 0, $p = 1){
		/* 标识正确性检测 */
		//if(!($id && is_numeric($id))){
		//	$this->error('文档ID错误！');
		//}

		/* 页码检测 */
		$p = intval($p);
		$p = empty($p) ? 1 : $p;

		/* 获取详细信息 */
		$Document = D('Document');
		$info = $Document->detail($id);
		if(!$info){
			//$this->error($Document->getError());
			return $this->_empty($Document->getError());
		}

		/* 分类信息 */
		$category = $this->category($info['category_id']);

		/* 获取模板 */
		if(!empty($info['template'])){//已定制模板
			$tmpl = $info['template'];
		} elseif (!empty($category['template_detail'])){ //分类已定制模板
			$tmpl =self::TMPL_PATH. $category['template_detail'];
		} else { //使用默认模板
			$tmpl = self::TMPL_PATH .'detail'  ;//'Article/'. get_document_model($info['model_id'],'name') .'/detail';
		}
        $tmpl = I('tmpl')? self::TMPL_PATH.I('tmpl') : $tmpl;
		/* 更新浏览数 */
		$map = array('id' => $id);
		$Document->where($map)->setInc('view');


        //By.jingshuixian
        $id = $info['id'];
        $cid = $category['id'];
        $mid = $category['model'][0];
        $pid = $category['pid'];
        $tid = $category['tid'];
        $topCategory = $this->category($tid);
        $pCategory =  $pid ? $this->category($pid) : null ;
        /* 模板赋值并渲染模板 */
        $this->assign('id', $id);
        $this->assign('cid', $cid);
        $this->assign('mid', $mid);
        $this->assign('tid', $tid);
        $this->assign('pid', $pid);
        $this->assign('topCategory', $topCategory);
        $this->assign('pCategory', $pCategory);
        //By.jingshuixian


        $title = $info['title'];
        $description =$info['description'];
        $keywords = $info['keywords'];

        if(!empty($info['seo_title'])){
            $title = $info['seo_title'];
        }else{
            $title .='-'.  C('WEB_SITE_TITLE');
        }

        $addfind =array('%title%');
        $addreplace=array($info['title']);

        $title = format_config($title,$addfind,$addreplace);
        $description = format_config($description,$addfind,$addreplace);
        $keywords = format_config($keywords,$addfind,$addreplace);

        $this->assign('title',$title);
        $this->assign('description', $description);
        $this->assign('keywords', $keywords);

        //By.jingshuixian 首页SEO信息 end
        $info = format_item_data($info,'arc');
		/* 模板赋值并渲染模板 */
		$this->assign('category', $category);
		$this->assign('info', $info);
		$this->assign('page', $p); //页码
		$this->display($tmpl);
	}

    public function search($p=1){

        $keyword =  I("keyword");
        $cate =  I("cate");
		
        $pagesize = 10;
        /* 分类信息 */
        //$category = $this->category(1);

        /* 获取当前分类列表 */
        $Document = M('Document');

        if(!empty($keyword)){
            $map['title|keywords']  =array('like','%'.$keyword.'%');
			//$map['keywords']  =array('like','%'.$keyword.'%');
			
            $map['status'] = array('eq',1,'and');
            //if($cate>0){
            //    $map['category_id'] =  $cate;
            //}
			//$map = "(title like '%".$keyword."%') OR (keywords like '%".$keyword." %')";
			
			//$map .= " AND  status = 1 ";
			$list = $Document->page($p,$pagesize)->where($map)->select();
			
            if(false === $list){
                //$this->error('获取列表数据失败！');
				return $this->_empty("获取搜索列表数据失败！");
            }

            $count =$Document->where($map)->count();

            $Page       = new \Think\PageCms($count,$pagesize);// 实例化分页类 传入总记录数和每页显示的记录数(25)
            $show       = $Page->show_yii();// 分页显示输出
            $list = forma_list_data($list,'arc'); //格式化数据
        }


        //$topcategory = got_top_nav($category['id']);

        $tmpl = 'search';
		$topCategory['title']="搜索";
		$topCategory['title_en']="search";
        /* 模板赋值并渲染模板 */
        $this->assign('title','搜索关键词：'. $keyword);
        $this->assign('keyword', $keyword);
        $this->assign('topCategory', $topCategory);
        $this->assign('category', $topCategory);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('list', $list);
        $this->assign("count",$count);

        $this->display(self::TMPL_PATH .$tmpl);
    }
	
	
	
	
	

    /**
     * 获取图集json信息你
     * @param int $id 文章ID
     * @param string $tuji_field 图集字段名称
     */
    public function get_imgs( $id=0 ,$tuji_field = "tuji"){
        $where["id"] = $id;
        $info = D("document_article")->where($where)->find();
		$document = D("document")->where($where)->find();
        $tuji =$info[$tuji_field];
        $tuji = str2arr($tuji);
        $list = array();
        foreach($tuji as $tuid){
            $img = get_cover($tuid);
            //echo $img ;
            $img_arr = array();
            $img_arr['alt']="图片";
            $img_arr['thumbs']=$img;
            $img_arr['dataImage']=$img;
            $img_arr['dataDescription']=_substr($document["desc"],100,"...");
            $list[] = $img_arr;
        }
        //var_dump($list);
        //echo $tuji;
        echo json_encode($list);
    }

	/* 文档分类检测 */
	private function category($id = 0){
		/* 标识正确性检测 */
		$id = $id ? $id : I('get.category', 0);
		if(empty($id)){
			//$this->error('没有指定文档分类！');
			return $this->_empty("没有指定文档分类！");
		}

		/* 获取分类信息 */
		$category = D('Category')->info($id);
		if($category && 1 == $category['status']){
			switch ($category['display']) {
				case 0:
					//$this->error('该分类禁止显示！');
					return $this->_empty("该分类禁止显示！");
					break;
				//TODO: 更多分类显示状态判断
				default:
                    //By.jingshuixian 格式化内容
                    $category = format_item_data($category,'cate');
					return $category;
			}
		} else {
			//$this->error('分类不存在或被禁用！');
			return $this->_empty("分类不存在或被禁用！");
		}
	}

}
