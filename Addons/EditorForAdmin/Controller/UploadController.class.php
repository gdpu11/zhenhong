<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Addons\EditorForAdmin\Controller;
use Home\Controller\AddonsController;
use Think\Upload;
require_once __DIR__ . '/Qiniu/autoload.php';

use Qiniu\Auth;

class UploadController extends AddonsController{

	public $uploader = null;

	/* 上传图片 */
	public function upload(){
		session('upload_error', null);
		/* 上传配置 */
		$setting = C('EDITOR_UPLOAD');

		/* 调用文件上传组件上传文件 */
		$this->uploader = new Upload($setting, 'Local');
		$info   = $this->uploader->upload($_FILES);
		if($info){
			$url = C('EDITOR_UPLOAD.sitePath').C('EDITOR_UPLOAD.rootPath').$info['imgFile']['savepath'].$info['imgFile']['savename'];
			$url = str_replace('./', '/', $url);
			$info['fullpath'] = __ROOT__.$url;
		}
		session('upload_error', $this->uploader->getError());
		return $info;
	}

	//keditor编辑器上传图片处理
	public function ke_upimg(){
		/* 返回标准数据 */
		$return  = array('error' => 0, 'info' => '上传成功', 'data' => '');
		$img = $this->upload();
		/* 记录附件信息 */
		if($img){
			$return['url'] = $img['fullpath'];
			unset($return['info'], $return['data']);
		} else {
			$return['error'] = 1;
			$return['message']   = session('upload_error');
		}

		/* 返回JSON数据 */
		exit(json_encode($return));
	}

	//ueditor编辑器上传图片处理
	public function ue_upimg(){

		$img = $this->upload();
		$return = array();
		$return['url'] = $img['fullpath'];
		$title = htmlspecialchars($_POST['pictitle'], ENT_QUOTES);
		$return['title'] = $title;
		$return['original'] = $img['imgFile']['name'];
		$return['state'] = ($img)? 'SUCCESS' : session('upload_error');
		/* 返回JSON数据 */
		$this->ajaxReturn($return);
	}

    public function wang_upimg(){
        /* 返回标准数据 */
        $return  = array('error' => 0, 'info' => '上传成功', 'data' => '');
        $img = $this->upload();
        /* 记录附件信息 */
        if($img){
            $wangEditorH5File = $img['wangEditorH5File'];

            $return['url'] = $img['fullpath'].$wangEditorH5File['savepath'].$wangEditorH5File['savename'];
            //unset($return['info'], $return['data']);
            echo $return['url'];
        } else {
            //$return['error'] = 1;
            //$return['message']   = session('upload_error');
            echo 'error|'.session('upload_error');
        }

        /* 返回JSON数据 */
        //exit(json_encode($return));
    }

    public function ue_upimg_action($action=""){
        $action = I("get.action");
        $jsonstr =file_get_contents("./Addons/EditorForAdmin/Controller/config.json");
        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", $jsonstr), true);

        switch ($action) {
            case 'config':
                $result =  json_encode($CONFIG);
                break;

            /* 上传图片 */
            case 'uploadimage':
                $img = $this->upload();  //upfile
                $upfile = $img['upfile'];
                $result['url'] = $img['fullpath'].$upfile['savepath'].$upfile['savename'];
                $title = htmlspecialchars($_POST['pictitle'], ENT_QUOTES);
                $result['title'] = $title;
                $result['original'] = $upfile['name'];
                $result['state'] = ($upfile)? 'SUCCESS' : session('upload_error');
                $result['type'] = $upfile['ext'];
                $result['size'] = $upfile['size'];
                break;

            default:
                $result = json_encode(array(
                    'state'=> '请求地址出错'
                ));
                break;
        }


        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            if(is_array($result)){
                $result = json_encode($result);
            }
            echo $result;
        }


    }

    /**
     * 七牛云存储
     * @param string $action
     *
     */
    public function qiniu($action="uploadToken"){

        if($action == "uploadToken"){

            $accessKey = 'QfbGDnFG58UqdTifPZAK5ChXA5pADofCy5BQzgXH';
            $secretKey = 'osyLkc_wbMQLOf5YdYa42tq9tb6xt7p-_xxPFQ5r';
            $auth = new Auth($accessKey, $secretKey);
            $bucket = 'demo';
            $upToken = $auth->uploadToken($bucket);

            $t = array("uptoken"=>$upToken);
            echo json_encode($t);

        }elseif($action == "video"){


        }
        else{

            echo "action error";

        }


    }
}
