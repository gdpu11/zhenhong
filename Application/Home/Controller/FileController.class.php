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
 * 文件控制器
 * 主要用于下载模型的文件上传和下载
 */

class FileController extends HomeController {
	/* 文件上传 */
	public function upload(){
		$return  = array('status' => 1, 'info' => '上传成功', 'data' => '');
		/* 调用文件上传组件上传文件 */
		$File = D('File');
		$file_driver = C('DOWNLOAD_UPLOAD_DRIVER');
		$info = $File->upload(
			$_FILES,
			C('DOWNLOAD_UPLOAD'),
			C('DOWNLOAD_UPLOAD_DRIVER'),
			C("UPLOAD_{$file_driver}_CONFIG")
		);

		/* 记录附件信息 */
		if($info){
            $return['data'] = $info['download']['id'] ; //think_encrypt(json_encode($info['download']));
            $return['info'] = $info['download']['name'];
		} else {
			$return['status'] = 0;
			$return['info']   = $File->getError();
		}

		/* 返回JSON数据 */
		$this->ajaxReturn($return);
	}

    /* 文件上传 */
    public function webuploader(){
        $return  = array("jsonrpc" => "2.0");
        /* 调用文件上传组件上传文件 */
        $File = D('File');
        $file_driver =  'Webuploader'; //'Webuploader'; //
        $info = $File->upload(
            $_FILES,
            C('DOWNLOAD_UPLOAD'),
            $file_driver,
            C("UPLOAD_{$file_driver}_CONFIG")
        );

        /* 记录附件信息 */
        if($info){

            $file = $info['file'];
            $return['result'] = null;

            $return +=$file;


            //print_r($info);
            $this->ajaxReturn($return);
        } else {
            $return['status'] = 0;
            $return['info']   = $File->getError();
        }

        /* 返回JSON数据 */
        //$this->ajaxReturn($return);
    }



    /* 下载文件 */
	public function download($id = null){
		if(empty($id) || !is_numeric($id)){
			$this->error('参数错误！');
		}

		$logic = D('Download', 'Logic');
		if(!$logic->download($id)){
			$this->error($logic->getError());
		}

	}

    /**
     * @param null $id  file表 文件ID
     * by.jingshuixian
     */
    public function download_by_file_id($id = null){
        if(empty($id) || !is_numeric($id)){
            $this->error('参数错误！');
        }

        $File = D('File');
        $root = C('DOWNLOAD_UPLOAD.sitePath'). C('DOWNLOAD_UPLOAD.rootPath');
        if(false === $File->download($root, $id)){
            $this->error = $File->getError();
        }

    }
}
