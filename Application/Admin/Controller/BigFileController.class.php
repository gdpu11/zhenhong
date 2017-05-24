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
 * 文件控制器
 * 主要用于下载模型的文件上传和下载
 */
class BigFileController extends AdminController
{

    /* 文件上传 */
    public function upload()
    {
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

// header("HTTP/1.0 500 Internal Server Error");
// exit;


// 5 minutes execution time  设置脚本最大执行时间
        @set_time_limit(5 * 60);

// Uncomment this one to fake upload time
// usleep(5000);

// Settings
// $targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
        $targetDir = RUNTIME_PATH . 'upload_tmp';   //缓存文件目录
        $uploadDir = RUNTIME_PATH . 'upload';       //上传文件目录

        $cleanupTargetDir = true; // Remove old files   删除旧的文件
        $maxFileAge = 5 * 3600; // Temp file age in seconds  临时文件最大缓存时间


// Create target dir
        if (!file_exists($targetDir)) {   //判断缓存目录是否创建
            @mkdir($targetDir);    //创建缓存目录
        }

// Create target dir
        if (!file_exists($uploadDir)) {    //创建上传目录
            @mkdir($uploadDir);
        }

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
        $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $savename;  //上传文件路径

// Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;     //当前分片位置
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;  //多少分片


// Remove old temp files
        if ($cleanupTargetDir) {  //删除旧的文件
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "未能打开临时目录。"}, "id" : "id"}');
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
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "未能打开临时目录。Failed to open output stream."}, "id" : "id"}');
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) { //$_FILES["file"]["error"]    //is_uploaded_file 函数判断指定的文件是否是通过 HTTP POST 上传的
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }

            // Read binary input stream and append it to temp file  读二进制输入流并将它附加到临时文件
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
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
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
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

            $file = M('file');
            $data['name'] = $fileName;
            $data['savename'] = $savename;
            //$data['']
        }

        // Return Success JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }

    public function uploadmd5(){

        // Make sure file is not cached (as it happens for example on iOS devices)
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

// header("HTTP/1.0 500 Internal Server Error");

// echo mymd5('upload/C程序设计语言.pdf'); die;
// Support CORS
// header("Access-Control-Allow-Origin: *");
// other CORS headers if any...
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit; // finish preflight CORS requests here
        }


        if ( !empty($_REQUEST[ 'debug' ]) ) {
            $random = rand(0, intval($_REQUEST[ 'debug' ]) );
            if ( $random === 0 ) {
                header("HTTP/1.0 500 Internal Server Error");
                exit;
            }
        }


// 5 minutes execution time
        @set_time_limit(5 * 60);

// Uncomment this one to fake upload time
// usleep(5000);

// Settings
// $targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
        $targetDir =RUNTIME_PATH . 'upload_tmp';
        $uploadDir = RUNTIME_PATH .'upload';

        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds


// Create target dir
        if (!file_exists($targetDir)) {
            @mkdir($targetDir);
        }

// Create target dir
        if (!file_exists($uploadDir)) {
            @mkdir($uploadDir);
        }

// Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }

        $md5File = @file('md5list2.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $md5File = $md5File ? $md5File : array();

        if (isset($_REQUEST["md5"]) && array_search($_REQUEST["md5"], $md5File ) !== FALSE ) {
            die('{"jsonrpc" : "2.0", "result" : null, "id" : "id", "exist": 1}');
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

// Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;


// Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
            }

            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}.part") {
                    continue;
                }

                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }


// Open temp file
        if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }

            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }

        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

// Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off
            rename("{$filePath}.part", $filePath);

            rename($filePath, $uploadPath);
            array_push($md5File, mymd5($uploadPath));
            $md5File = array_unique($md5File);
            file_put_contents('md5list2.txt', join($md5File, "\n"));
        }

        function mymd5( $file ) {
            $fragment = 65536;

            $rh = fopen($file, 'rb');
            $size = filesize($file);

            $part1 = fread( $rh, $fragment );
            fseek($rh, $size-$fragment);
            $part2 = fread( $rh, $fragment);
            fclose($rh);

            return md5( $part1.$part2 );
        }



// Return Success JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');

    }


}
