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
 * 工具扩展控制器
 */
class ToolsController extends Controller {
    /**
     * 根据文章ID获得字段的信息 返回json信息
     * @param int $id
     * @param string $field
     * @param string $type
     */
    public function getFieldJsonById( $id=0 ,$field = "tuji" ,$type='imgs'){

        $document = D("document")->detail($id);
        if($type == 'imgs'){  //layer img数据格式
            $field_data = str2arr($document[$field]);
            $list['title'] = $document['title'];
            $list['id'] = $document['id'];
            $list['start'] = 0;

            foreach($field_data as $tuid){
                $img = get_cover($tuid);
                $img_arr = array();
                $img_arr['alt']="图片";
                $img_arr['thumbs']=$img;
                $img_arr['src']=$img;

                $list['data'][] = $img_arr;
            }
        }

        echo json_encode($list);


    }

    public function video($url="http://oio3wdesm.bkt.clouddn.com/zhenyi.mp4",$w="100%" ,$h="100%"){

        $static = "/Public/static/";

        $path = $static . "ckplayer";
        $is_mobile =  is_mobile()?'true':'false';
        $video = "ckplayer_id";
        $url = base64_decode($url);
        $html = <<<php
<div id="$video"></div>
    <script type="text/javascript" src="$path/ckplayer.js" charset="utf-8"></script>
    <script type="text/javascript" src="$static/jquery-1.10.2.min.js" charset="utf-8"></script>
    <script type="text/javascript">


 //注意：下面的代码是放在iframe引用的子页面中调用
$(window.parent.document).find(".wangEditor_ckplayer").load(function () {

    if($is_mobile){

    var main = $(window.parent.document).find(".wangEditor_ckplayer");
    var thisheight = $(document).height() + 30;
    //main.height(thisheight);
    main.width($(window.parent.document).width());
    }

    var flashvars={
            f:'$url',
            c:0,
            b:1,
            i:''
        };
        var params={bgcolor:'#FFF',allowFullScreen:true,allowScriptAccess:'always',wmode:'transparent'};
        //CKobject.embedSWF('$path/ckplayer.swf','$video','ckplayer_a1','$w','$h',flashvars,params);
        /*
         CKobject.embedSWF(播放器路径,容器id,播放器id/name,播放器宽,播放器高,flashvars的值,其它定义也可省略);
         下面三行是调用html5播放器用到的
         */
        var video=['$url'];
        //var support=['iPad','iPhone','ios','android+false','msie10+false'];
        var support=['all'];
        CKobject.embedHTML5('$video','ckplayer_a1','$w','$h',video,flashvars,support);




});



    </script>


php;

        echo $html;

    }
	
	
	public function baidumap($name,$point="",$width="100%" ,$height="100%"){

        $point = str2arr($point);


        $this->assign('name', $name);
        $this->assign('point', $point);
        $this->assign('width', $width);
        $this->assign('height', $height);


		$this->display('Public/baidumap');
		
	}


    public function cropperjs($path='',$savePath="",$width=1,$height=1,$toDataURL = ""){

        if(IS_POST){

            $url = explode(',',$toDataURL);
            $a = file_put_contents('./test.jpg', base64_decode($url[1]));//返回的是字节数
            print_r($a);


        }else{
            $content = <<<php
<script type="text/javascript" src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js" charset="utf-8"></script>
<link rel="stylesheet" href="__STATIC__/cropperjs/cropper.css">
<script src="__STATIC__/cropperjs/cropper.js"></script>

<div>

<span class="saveimg">保存图片</span>

</div>

<div id="container">
<img id="container_img" src="$path">
</div>
<script>
/**
 var image = document.getElementById('container_img');
                var cropBoxData;
                var canvasData;
                var cropper;

                cropper = new Cropper(image, {
                    autoCropArea: 0.5,
                    ready: function () {
                        cropper.setCropBoxData(cropBoxData).setCanvasData(canvasData);
                    }
                });




   });
***/

var image =   $('#container_img').cropper({
  aspectRatio: $width / $height,
  crop: function(e) {
  }
});

   $('.saveimg').click(function(){



    var canvasData = image.cropper("getCroppedCanvas").toDataURL();

    console.log(canvasData);
    $.post("/tools/cropperjs", { toDataURL: canvasData , path :'$path'},
          function(data){
          alert("Data Loaded: " + data);
          });

   });

</script>
php;

            $this->show($content);

        }

    }

    /**
     * dedecms数据转换程序
     */
    public function dedeto(){
        $typeid = 27 ;

        $model_id = 8;
        $type = 2;
        $category_id = 8 ;


        $list = M('archives')->where('typeid='.$typeid)->select();

        foreach($list as $key => $item){

            echo $item['id']."<br>" ;

            $document = M('document');

            $document_data['uid'] = 1;
            $document_data['title'] = $item['title'];
            $document_data['category_id'] = $category_id;
            $document_data['keywords'] = $item['keywords'];
            $document_data['description'] = $item['description'];
            $document_data['pid'] = 0;
            $document_data['model_id'] = $model_id;
            $document_data['type'] = $type;
            $document_data['create_time'] = $item['senddate'];
            $document_data['update_time'] = $item['pubdate'];
            $document_data['status'] = 1;
            $document_data['display'] = 1;
            $document_data['dedecover'] = $item['litpic'];
            $document_data['desc'] = $item['description'];

            $id = $document->add($document_data);


           $addcase =  M('addcase')->where('aid='.$item['id'])->find();

            $document_anli = M('documentAnli');

            $document_anli_data['id']= $id ;
            $document_anli_data['mingcheng']= $item['title'] ;
            $document_anli_data['dizhi']= $addcase['address'] ;
            $document_anli_data['content']= $addcase['body'] ;

            $document_anli->add($document_anli_data);


        }

        echo "<br>转换完成" ;

    }

    /**
     * dedecms 新闻  数据转换程序
     */
    public function dedetonews(){
        $typeid = 21 ;

        $model_id = 2;
        $type = 2;
        $pid=18 ;
        $category_id = 19 ;


        $list = M('archives')->where('typeid='.$typeid)->select();

        foreach($list as $key => $item){

            echo $item['id']."<br>" ;

            $document = M('document');

            $document_data['uid'] = 1;
            $document_data['title'] = $item['title'];
            $document_data['category_id'] = $category_id;
            $document_data['keywords'] = $item['keywords'];
            $document_data['description'] = $item['description'];
            $document_data['pid'] = $pid;

            $document_data['model_id'] = $model_id;
            $document_data['type'] = $type;
            $document_data['create_time'] = $item['senddate'];
            $document_data['update_time'] = $item['pubdate'];
            $document_data['status'] = 1;
            $document_data['display'] = 1;
            $document_data['dedecover'] = $item['litpic'];
            $document_data['desc'] = $item['description'];

            $id = $document->add($document_data);


            $addcase =  M('addonarticle')->where('aid='.$item['id'])->find();

            $document_anli = M('documentArticle');

            $document_anli_data['id']= $id ;
            //$document_anli_data['mingcheng']= $item['title'] ;
            //$document_anli_data['dizhi']= $addcase['address'] ;
            $document_anli_data['content']= $addcase['body'] ;

            $document_anli->add($document_anli_data);


        }

        echo "<br>转换完成" ;

    }

    /*老数据装换**/
    public function oldto(){
        $typeid = 17 ;

        $model_id = 8;
        $type = 2;
        $category_id = 12 ;


        $list = M('documentmy')->where('category_id='.$typeid)->select();

        foreach($list as $key => $item){

            echo $item['id']."<br>" ;

            $document = M('document');

            $document_data['uid'] = 1;
            $document_data['title'] = $item['title'];
            $document_data['category_id'] = $category_id;
            $document_data['keywords'] = $item['keywords'];
            $document_data['description'] = $item['description'];
            $document_data['pid'] = 0;
            $document_data['model_id'] = $model_id;
            $document_data['type'] = $type;
            $document_data['create_time'] = $item['create_time'];
            $document_data['update_time'] = $item['update_time'];
            $document_data['status'] = 1;
            $document_data['display'] = 1;
            //$document_data['dedecover'] = $item['litpic'];
            $document_data['dedecover'] = get_cover($item['cover_id']);
            $document_data['dedecover'] = substr( $document_data['dedecover'],21);
            $document_data['desc'] = $item['description'];


            $id = $document->add($document_data);


            $addcase =  M('documentmyAnli')->where('id='.$item['id'])->find();

            $document_anli = M('documentAnli');

            $document_anli_data['id']= $id ;
            $document_anli_data['mingcheng']= $item['title'] ;
            $document_anli_data['dizhi']= $addcase['address'] ;
            $document_anli_data['content']= $addcase['content'] ;

            $document_anli->add($document_anli_data);


        }

        echo "<br>转换完成" ;

    }


}
