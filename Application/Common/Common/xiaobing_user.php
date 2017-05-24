<?php
/**
 * User: jingshuixian
 * Date: 2016/2/17
 * Time: 12:15
 */

/**
 * 根据用户ID获取用户名
 * @param  integer $uid 用户ID
 * @return string       用户名
 */
function get_username($uid = 0){
    static $list;
    if(!($uid && is_numeric($uid))){ //获取当前登录用户名
        return session('user_auth.username');
    }

    /* 获取缓存数据 */
    if(empty($list)){
        $list = S('sys_active_user_list');
    }

    /* 查找用户信息 */
    $key = "u{$uid}";
    if(isset($list[$key])){ //已缓存，直接使用
        $name = $list[$key];
    } else { //调用接口获取用户信息
        $User = new User\Api\UserApi();
        $info = $User->info($uid);
        if($info && isset($info[1])){
            $name = $list[$key] = $info[1];
            /* 缓存用户 */
            $count = count($list);
            $max   = C('USER_MAX_CACHE');
            while ($count-- > $max) {
                array_shift($list);
            }
            S('sys_active_user_list', $list);
        } else {
            $name = '';
        }
    }
    return $name;
}

/**
 * 根据用户ID获取用户昵称
 * @param  integer $uid 用户ID
 * @return string       用户昵称
 */
function get_nickname($uid = 0){
    static $list;
    if(!($uid && is_numeric($uid))){ //获取当前登录用户名
        return session('user_auth.username');
    }

    /* 获取缓存数据 */
    if(empty($list)){
        $list = S('sys_user_nickname_list');
    }

    /* 查找用户信息 */
    $key = "u{$uid}";
    if(isset($list[$key])){ //已缓存，直接使用
        $name = $list[$key];
    } else { //调用接口获取用户信息
        $info = M('Member')->field('nickname')->find($uid);
        if($info !== false && $info['nickname'] ){
            $nickname = $info['nickname'];
            $name = $list[$key] = $nickname;
            /* 缓存用户 */
            $count = count($list);
            $max   = C('USER_MAX_CACHE');
            while ($count-- > $max) {
                array_shift($list);
            }
            S('sys_user_nickname_list', $list);
        } else {
            $name = '';
        }
    }
    return $name;
}

/**
 * @param $fileid
 */
function get_file_url($fileid=0){
    $url = "#";
    if(!empty($fileid)){
        $url = U('File/download_by_file_id?id=' . $fileid);
    }
    return $url;
}

/**
 * 根据文件ID获得文件名称
 * @param int $fileid
 * @return string
 */
function get_file_name($fileid=0){
    $title = '';
    if(!empty($fileid)){
        $file = D('file');
        $title = $file->getFieldById($fileid,'name');

    }
    return $title;
}
function get_file_ext($fileid=0){
    $title = '';
    if(!empty($fileid)){
        $file = D('file');
        $title = $file->getFieldById($fileid,'ext');

    }
    return $title;
}

function get_file_path($fileid=0){
    $path = '';
    if(!empty($fileid)){
        $file = D('file');
        $savename =  $file->getFieldById($fileid,'savename');
        $savepath =  $file->getFieldById($fileid,'savepath');
        $path = $savepath. $savename;
    }
    return LONG_WEB_SITE_STATIC.C('DOWNLOAD_UPLOAD.rootPath').$path;
}