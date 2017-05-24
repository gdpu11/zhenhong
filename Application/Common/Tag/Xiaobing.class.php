<?php
/**
 * Created by PhpStorm.
 * User: jingshuixian
 * Date: 2016/7/29
 * Time: 17:20
 */
namespace Common\Tag;
use \Think\Template\TagLib;
use \Think\View;
class Xiaobing extends TagLib{

// 定义标签
    protected $tags=array(
        'webuploader'=>array('attr'=>'name,value,type,picker','close'=>1),
        'cuplayer'=>array('attr'=>'id,CuPlayerFile,CuPlayerImage,CuPlayerWidth,CuPlayerHeight,CuPlayerAutoPlay','close'=>1),
        'xiaobing'=>array('attr'=>'color','close'=>0),
        'baidumap'=>array('attr'=>'id,content,title,lat,lng,width,height','close'=>0),
        'xarae'=>array('attr'=>'id','close'=>0),
    );

    /**
     * @视频播放器
     * @param $tag
     * @param $content
     */
    public function _cuplayer($tag, $content){
        $path = "/Public/static/CuPlayer";
        $id = empty($tag['id'])?"CuPlayer":$tag['id'];
        $CuPlayerFile= empty($tag['cuplayerfile'])?"":$tag['cuplayerfile'];
        $CuPlayerWidth = empty($tag['cuplayerwidth'])?"450":$tag['cuplayerwidth'];
        $CuPlayerHeight = empty($tag['cuplayerheight'])?"300":$tag['cuplayerheight'];
        $CuPlayerAutoPlay = empty($tag['cuplayerautoplay'])?"no":$tag['cuplayerautoplay'];

        $CuPlayerImage = empty($tag['cuplayerimage'])?$path."/images/start.jpg":$tag['cuplayerimage'];

        //if(strpos($CuPlayerFile,'$') >= 0){
          // $CuPlayerFile = "{".$CuPlayerFile."}";
        //}

        $str=<<<php

    <script type="text/javascript" src="$path/images/swfobject.js"></script>
    <div class="video" id="$id"><strong> 提示：您的Flash Player版本过低，请进行网页播放器升级！</strong></div>
    <script type="text/javascript">
    var so = new SWFObject("$path/CuPlayerMiniV4.swf","CuPlayerV4","$CuPlayerWidth","$CuPlayerHeight","9","#000000");
    so.addParam("allowfullscreen","true");
    so.addParam("allowscriptaccess","always");
    so.addParam("wmode","opaque");
    so.addParam("quality","high");
    so.addParam("salign","lt");
    so.addVariable("CuPlayerSetFile","$path/CuPlayerSetFile.xml"); //播放器配置文件地址
    so.addVariable("CuPlayerFile","$CuPlayerFile"); //视频文件地址
    so.addVariable("CuPlayerImage","$CuPlayerImage");//视频略缩图
    so.addVariable("CuPlayerWidth","$CuPlayerWidth"); //视频宽度
    so.addVariable("CuPlayerHeight","$CuPlayerHeight"); //视频高度
    so.addVariable("CuPlayerAutoPlay","$CuPlayerAutoPlay"); //是否自动播放
    so.write("$id");
    </script>
php;
        return $str;

    }
    /**
     * 大文件上传
     */
    public function _webuploader($tag, $content){

        $path = "/Public/static/WebUploader";
        $value = $tag['value'] ;
        $name = $tag['name'];
        if(empty($name)) {
            return "name 不能为空";
        }
        //选择文本提示
        $picker = empty($tag['picker'])?"选择文件":$tag['picker'];

        $str=<<<php
        <link rel="stylesheet" href="$path/css/webuploader.css">
        <link rel="stylesheet" href="$path/css/xiaobing.css">
<script src="$path/js/webuploader.js"></script>

<div id="uploader_$name" class="xiaobing_uploader">
    <!--用来存放文件信息-->
    <div class="btns">
        <div id="picker_$name">$picker</div>
    </div>
    <div id="theState_$name" class="uploader-State"></div>
    <input type="hidden" name="$name" value="$value">
</div>


<script>
    var BASE_URL  = "$path";
    var theState_$name = $('#theState_$name');
    var __fileid_$name = "$name";
var uploader_$name = WebUploader.create({

    // swf文件路径
    swf: BASE_URL + '/js/Uploader.swf',

    // 文件接收服务端。
    server: '/File/webuploader',

    // 选择文件的按钮。可选。
    // 内部根据当前运行是创建，可能是input元素，也可能是flash.
    pick: '#picker_$name',

    // 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
    resize: false,
    // 选完文件后，是否自动上传。
    auto: true,
     chunked: true,  //是否分片
     chunkSize: 512 * 1024, //每片大小
});


// 当有文件被添加进队列的时候
uploader_$name.on( 'fileQueued', function( file ) {
    theState_$name.html("已经添加");
});

uploader_$name.onUploadProgress = function( file, percentage ) {
         theState_$name.html("上传中");
    };


uploader_$name.on( 'uploadSuccess', function( file ,data) {

			theState_$name.html("处理中");

			$("input[name="+__fileid_$name+"]").val(data.id);
			//$("input[name=size]").val(data.size);


		});

uploader_$name.onError = function (code) {
			 if(code ==='Q_TYPE_DENIED'){
				alert("文件格式错误");
			 }else{
                 alert("未知错误:"+code);
            }

        };

uploader_$name.on( 'uploadComplete', function( file ) {
    theState_$name.html('已上传:'+file.name);

});

</script>
php;
        return $str;

    }

    public function _xiaobing($tag, $content){
        $color= empty($tag['color'])?"":$tag['color'];
        $str=<<<php
<span class="jishuzhichi">技术支持：<a href="http://www.xbjianzhan.com" title="成都网站建设" target="_blank">成都网站建设</a><a href="http://www.xbjianzhan.com" title="龙兵科技" target="_blank">龙兵科技</a></span>
<style>
.jishuzhichi{color:$color;}
.jishuzhichi a{color:$color;text-decoration:none;}
.jishuzhichi a:hover{color: $color;text-decoration:none;}
</style>
php;
        return $str ;
    }


    public function _baidumap($tag, $content){
        //'baidumap'=>array('attr'=>'id,content,title,lat,lng,width,height','close'=>0)

        $id= empty($tag['id'])?"baidumap":$tag['id'];
        $content = empty($tag['content'])?"":$tag['content'];
        $title= empty($tag['title'])?"":$tag['title'];
        $lat= empty($tag['lat'])?"0":$tag['lat'];
        $lng= empty($tag['lng'])?"0":$tag['lng'];
        $width= empty($tag['width'])?"100%":$tag['width'];
        $height= empty($tag['height'])?"300px":$tag['height'];

        $str=<<<php
            <!--引用百度地图API-->
            <script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=2xXltyLOW7M6glTiQV14k8nfk4T9fMNI"></script>
            <!--百度地图容器-->
            <!-- bug修复 -->
            <style>
            #$id,#$id div,#$id span,#$id label,#$id img{
              -webkit-box-sizing: content-box;
              -moz-box-sizing: border-box;
               box-sizing: content-box;
               max-width:initial;
             }
            </style>
            <div style="width:$width;height:$height;font-size:12px" id="$id"></div>
            <script type="text/javascript">
              //创建和初始化地图函数：
              function initMap(){
                createMap();//创建地图
                setMapEvent();//设置地图事件
                addMapControl();//向地图添加控件
                addMapOverlay();//向地图添加覆盖物
              }
              function createMap(){
                map = new BMap.Map("$id");
                map.centerAndZoom(new BMap.Point($lng,$lat),15);
              }
              function setMapEvent(){
                map.enableScrollWheelZoom();
                map.enableKeyboard();
                map.enableDragging();
                map.enableDoubleClickZoom()
              }
              function addClickHandler(target,window){
                target.addEventListener("click",function(){
                  target.openInfoWindow(window);
                });
              }
              function addMapOverlay(){
                var markers = [
                  {content:"$content",title:"$title",imageOffset: {width:-23,height:-21},position:{lat:$lat,lng:$lng}}
                ];
                for(var index = 0; index < markers.length; index++ ){
                  var point = new BMap.Point(markers[index].position.lng,markers[index].position.lat);
                  var marker = new BMap.Marker(point,{icon:new BMap.Icon("http://api.map.baidu.com/lbsapi/createmap/images/icon.png",new BMap.Size(20,25),{
                    imageOffset: new BMap.Size(markers[index].imageOffset.width,markers[index].imageOffset.height)
                  })});
                  var label = new BMap.Label(markers[index].title,{offset: new BMap.Size(25,5)});
                  var opts = {
                    width: 200,
                    title: markers[index].title,
                    enableMessage: false
                  };
                  var infoWindow = new BMap.InfoWindow(markers[index].content,opts);
                  marker.setLabel(label);
                  addClickHandler(marker,infoWindow);
                  map.addOverlay(marker);
                };
              }
              //向地图添加控件
              function addMapControl(){
                var scaleControl = new BMap.ScaleControl({anchor:BMAP_ANCHOR_BOTTOM_LEFT});
                scaleControl.setUnit(BMAP_UNIT_IMPERIAL);
                map.addControl(scaleControl);
                var navControl = new BMap.NavigationControl({anchor:BMAP_ANCHOR_TOP_LEFT,type:0});
                map.addControl(navControl);
                var overviewControl = new BMap.OverviewMapControl({anchor:BMAP_ANCHOR_BOTTOM_RIGHT,isOpen:false});
                map.addControl(overviewControl);
              }
              var map;
                initMap();
            </script>

php;

        return $str;

    }

    /***
     * @param $tag
     * @param $content
     */
    public function _xarae($tag, $content){
        $id = $tag['id'] ;
        $str = '' ;
        //如果区域ID是函数  就执行函数获取值
        if(0 === strpos($id,':')){
            // 采用函数定义
            $id = eval('return '.substr($id,1).';');
        }
        //如果区域ID 为 空 就返回空
        if(empty($id)) {
            return ;
        }

        //多个模块ID
        if(strpos($id,',') > 0){
            $ids = str2arr($id);
            foreach($ids as $vo){
                $str .= $this->get_tmpl($vo) ;
            }
        }else{
            $str = $this->get_tmpl($id) ;
        }

        //实例化视图类
        $view     =  new View();
        $str = $view->show('','','',$str,'');
        return $str;
    }

    private function get_tmpl($blockId=0){


        //获取模块数据
        $xblock = M('xblock');
        $block = $xblock->find($blockId);
        $attribute = $block['attribute'];

        if(!empty($block)){
            //获取模板数据
            $tmplId = $block['tmplid'];
            $xtmpl = M('xtmpl');
            $tmpl = $xtmpl->find($tmplId);

            $code = $tmpl['code'];
            $attribute = preg_split('/[\r\n]+/', trim($attribute, ",;\r\n"));
            $code = str_replace(array("#0#","#1#","#2#","#3#","#4#"),$attribute,$code);

        }
        return $code;

    }



}