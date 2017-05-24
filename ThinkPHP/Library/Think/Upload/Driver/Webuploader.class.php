<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Think\Upload\Driver;
class Webuploader{
    /**
     * 上传文件根目录
     * @var string
     */
    private $rootPath;

    /**
     * 本地上传错误信息
     * @var string
     */
    private $error = ''; //上传错误信息

    /**
     * 构造函数，用于设置上传根路径
     */
    public function __construct($config = null){

    }

    /**
     * 检测上传根目录
     * @param string $rootpath   根目录
     * @return boolean true-检测通过，false-检测失败
     */
    public function checkRootPath($rootpath){
        if(!(is_dir($rootpath) && is_writable($rootpath))){
            $this->error = '上传根目录不存在！请尝试手动创建:'.$rootpath;
            return false;
        }
        $this->rootPath = $rootpath;
        return true;
    }

    /**
     * 检测上传目录
     * @param  string $savepath 上传目录
     * @return boolean          检测结果，true-通过，false-失败
     */
    public function checkSavePath($savepath){
        /* 检测并创建目录 */
        if (!$this->mkdir($savepath)) {
            return false;
        } else {
            /* 检测目录是否可写 */
            if (!is_writable($this->rootPath . $savepath)) {
                $this->error = '上传目录 ' . $savepath . ' 不可写！';
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * 保存指定文件
     * @param  array   $file    保存的文件信息
     * @param  boolean $replace 同名文件是否覆盖
     * @return boolean          保存状态，true-成功，false-失败
     */
    public function save($file, $replace=true) {
        //return false ;
        // Make sure file is not cached (as it happens for example on iOS devices) 确保文件不会缓存(例如碰巧在iOS设备上)
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");


        // Support CORS
        // header("Access-Control-Allow-Origin: *");
        // other CORS headers if any...
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit; // finish preflight CORS requests here
        }


        if (!empty($_REQUEST['debug'])) {
            $random = rand(0, intval($_REQUEST['debug']));
            if ($random === 0) {
                header("HTTP/1.0 500 Internal Server Error");
                exit;
            }
        }

        @set_time_limit(5 * 60);

        // Uncomment this one to fake upload time
        // usleep(5000);

        // Settings
        // $targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";

        $targetDir = RUNTIME_PATH ;   //缓存文件目录
        //$uploadDir = './Runtime/upload';       //上传文件目录

        //$filename = $this->rootPath . $file['savepath'] . $file['savename'];

        $cleanupTargetDir = true; // Remove old files   删除旧的文件
        $maxFileAge = 5 * 3600; // Temp file age in seconds  临时文件最大缓存时间


// Create target dir
        if (!file_exists($targetDir)) {   //判断缓存目录是否创建
            @mkdir($targetDir);    //创建缓存目录
        }

// Create target dir
        //if (!file_exists($uploadDir)) {    //创建上传目录
         //   @mkdir($uploadDir);
       // }

// Get a file name                //获得一个文件名称
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }
        //获取保存文件名称

        $savename = time();

        $fileName = iconv('UTF-8', 'GB2312', $fileName);//转编码
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;    //缓存文件路径
        //$uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $savename;  //上传文件路径

        $uploadPath = $this->rootPath . $file['savepath'] . $file['savename'];

// Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;     //当前分片位置
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;  //多少分片


// Remove old temp files
        if ($cleanupTargetDir) {  //删除旧的文件
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                echo('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "未能打开临时目录。"}, "id" : "id"}');
                return false;
            }

            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // If temp file is current file proceed to the next  如果当前文件进行到下一个临时文件
                if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
                    continue;
                }

                // Remove temp file if it is older than the max age and is not the current file
//		删除超时的缓存文件

                if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }


// Open temp file
        if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
            echo('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "未能打开临时目录。Failed to open output stream."}, "id" : "id"}');
            return false;
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) { //$_FILES["file"]["error"]    //is_uploaded_file 函数判断指定的文件是否是通过 HTTP POST 上传的
                echo('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
                return false;
            }

            // Read binary input stream and append it to temp file  读二进制输入流并将它附加到临时文件
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                echo('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                return false;
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                echo ('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                return false;
            }
        }
//写出文件
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);
//重命名 缓存文件
        rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");

        $index = 0;
        $done = true;
        for ($index = 0; $index < $chunks; $index++) {
            if (!file_exists("{$filePath}_{$index}.part")) {  //函数检查文件或目录是否存在  检查分片是否完整
                $done = false;
                break;
            }
        }
        if ($done) {  //是完整的分片, 完整就开始合并分片
            if (!$out = @fopen($uploadPath, "wb")) { // 未能打开输出流。
                echo('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
                return false;
            }

              if (flock($out, LOCK_EX)) {   //php文件加锁 lock_sh ,lock_ex  共享锁和排他锁，也就是读锁(LOCK_SH)和写锁(LOCK_EX)
                             for ($index = 0; $index < $chunks; $index++) {  //合并分片
                    if (!$in = @fopen("{$filePath}_{$index}.part", "rb")) {
                        break;
                    }

                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }

                    @fclose($in);
                    @unlink("{$filePath}_{$index}.part");
                }

                flock($out, LOCK_UN);
            }
            @fclose($out);

            // Return Success JSON-RPC response  文件保存成功
            //echo('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');


            return true;
        }

        // Return Success JSON-RPC response   // 分片保存成功
        echo('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
         return false;
    }

    /**
     * 创建目录
     * @param  string $savepath 要创建的穆里
     * @return boolean          创建状态，true-成功，false-失败
     */
    public function mkdir($savepath){
        $dir = $this->rootPath . $savepath;
        if(is_dir($dir)){
            return true;
        }

        if(mkdir($dir, 0777, true)){
            return true;
        } else {
            $this->error = "目录 {$savepath} 创建失败！";
            return false;
        }
    }

    /**
     * 获取最后一次上传错误信息
     * @return string 错误信息
     */
    public function getError(){
        return $this->error;
    }

}
