<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Install\Controller;
use Think\Controller;
use Think\Db;
use Think\Storage;

class InstallController extends Controller{

    protected function _initialize(){
        if(Storage::has(LONG_WEB_SITE_LOCK)){
            $this->error('已经成功安装了OneThink，请不要重复安装!');
        }
    }

    //安装第一步，检测运行所需的环境设置
    public function step1(){
        session('error', false);

        //环境检测
        $env = check_env();

        //目录文件读写检测
        if(IS_WRITE){
            $dirfile = check_dirfile();
            $this->assign('dirfile', $dirfile);
        }

        //函数检测
        $func = check_func();

        session('step', 1);

        $this->assign('env', $env);
        $this->assign('func', $func);
        $this->display();
    }

    //安装第二步，创建数据库
    public function step2($db = null, $admin = null){
        if(IS_POST){

            //检测管理员信息
            if(!is_array($admin) || empty($admin[0]) || empty($admin[1]) || empty($admin[3])){
                $this->error('请填写完整管理员信息');
            } else if($admin[1] != $admin[2]){
                $this->error('确认密码和密码不一致');
            } else {
                $info = array();
                list($info['username'], $info['password'], $info['repassword'], $info['email'])
                = $admin;
                //缓存管理员信息
                session('admin_info', $info);
            }

            //检测数据库配置
            if(!is_array($db) || empty($db[0]) ||  empty($db[1]) || empty($db[2]) || empty($db[3])){
                $this->error('请填写完整的数据库配置');
            } else {
                $DB = array();
                list($DB['DB_TYPE'], $DB['DB_HOST'], $DB['DB_NAME'], $DB['DB_USER'], $DB['DB_PWD'],
                     $DB['DB_PORT'], $DB['DB_PREFIX']) = $db;
                //缓存数据库配置
                session('db_config', $DB);

                //创建数据库
                $dbname = $DB['DB_NAME'];
                unset($DB['DB_NAME']);
                $db  = Db::getInstance($DB);
                $sql = "CREATE DATABASE IF NOT EXISTS `{$dbname}` DEFAULT CHARACTER SET utf8";
                $db->execute($sql) || $this->error('创建数据库失败!!!请加 小兵建站CMS技术群(八) ： 321757896<br>'.$db->getError());
            }

            //跳转到数据库安装页面
            $this->redirect('step3');
        } else {
            if(session('update')){
                session('step', 2);
                $this->display('update');
            }else{
                session('error') && $this->error('环境检测没有通过，请调整环境后重试！');

                $step = session('step');
                if($step != 1 && $step != 2){
                    $this->redirect('step1');
                }

                session('step', 2);
                $this->display();
            }
        }
    }

    //安装第三步，安装数据表，创建配置文件
    public function step3(){
        if(session('step') != 2){
            $this->redirect('step2');
        }

        $this->display();

        if(session('update')){
            $db = Db::getInstance();
            //更新数据表
            update_tables($db, C('DB_PREFIX'));
        }else{
            //连接数据库
            $dbconfig = session('db_config');
            $db = Db::getInstance($dbconfig);
            //创建数据表
            create_tables($db, $dbconfig['DB_PREFIX']);
            //注册创始人帐号
            $auth  = "";//build_auth_key();
            $admin = session('admin_info');
            register_administrator($db, $dbconfig['DB_PREFIX'], $admin, $auth);

            //创建配置文件
            $conf   =   write_config($dbconfig, $auth);
            session('config_file',$conf);

        }

        if(session('error')){
            //show_msg();
        } else {
            session('step', 3);
            $this->redirect('Index/complete');
        }
    }

    public function check_password($db = null){
        //$conn = @mysql_connect($dbhost,$dbuser,$dbpwd);
        if(!is_array($db) || empty($db[0]) ||  empty($db[1]) || empty($db[2]) || empty($db[3])){
            $this->error("<font color='red'>请填写完整的数据库配置</font>");
        } else {
            $DB = array();
            list($DB['DB_TYPE'], $DB['DB_HOST'], $DB['DB_NAME'], $DB['DB_USER'], $DB['DB_PWD'],
                $DB['DB_PORT'], $DB['DB_PREFIX']) = $db;

            $conn = @mysql_connect($DB['DB_HOST'].':'.$DB['DB_PORT'],$DB['DB_USER'],$DB['DB_PWD']);
            if($conn)
            {
                $dbname = $DB['DB_NAME'];
                if(empty($dbname)){
                    $this->error("<font color='green'>数据库信息正确！</font>");
                }else{
                    $info = mysql_select_db($dbname,$conn)?"<font color='yellow'>数据库已经存在，系统将覆盖数据库</font>":"<font color='green'>数据库不存在,系统将自动创建</font>";
                    $this->success($info);
                }
            }else{
                $this->error("<font color='red'>数据库连接失败！</font>");
            }

        }

    }

    public function send_mail($db = null, $admin = null){
        /*授权*/
        $host = strtolower($_SERVER['HTTP_HOST']) ;
        $email = $admin[3];
        $url =  "http://www.xiaobing360.com/api.php?type=mail&host=$host&email=$email";
        $data = @file_get_contents($url);
        echo $data;

    }
    /**
     * 获取当前页面完整URL地址
     */
    protected function get_url() {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
    }

}
