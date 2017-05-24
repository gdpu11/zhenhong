<?php
echo "T_B_I_D : 陈氏之策";


/**
 * 系统公共库文件
 * 主要定义系统公共函数库
 */

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_login(){
    $user = session('user_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session('user_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
    }
}

/**
 * 检测当前用户是否为管理员
 * @return boolean true-管理员，false-非管理员
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_administrator($uid = null){
    $uid = is_null($uid) ? is_login() : $uid;
    return $uid && (intval($uid) === C('USER_ADMINISTRATOR'));
}

/**
 * 字符串转换为数组，主要用于把分隔符调整到第二个参数
 * @param  string $str  要分割的字符串
 * @param  string $glue 分割符
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function str2arr($str, $glue = ','){
    return explode($glue, $str);
}

/**
 * 数组转换为字符串，主要用于把分隔符调整到第二个参数
 * @param  array  $arr  要连接的数组
 * @param  string $glue 分割符
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function arr2str($arr, $glue = ','){
    return implode($glue, $arr);
}

/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start, $length, $charset="utf-8", $suffix=true) {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice.'...' : $slice;
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 单位 秒
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function think_encrypt($data, $key = '', $expire = 0) {
    $key  = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
    $data = base64_encode($data);
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($key);
    $char = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }

    $str = sprintf('%010d', $expire ? $expire + time():0);

    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1)))%256);
    }
    return str_replace(array('+','/','='),array('-','_',''),base64_encode($str));
}

/**
 * 系统解密方法
 * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param  string $key  加密密钥
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function think_decrypt($data, $key = ''){
    $key    = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
    $data   = str_replace(array('-','_'),array('+','/'),$data);
    $mod4   = strlen($data) % 4;
    if ($mod4) {
       $data .= substr('====', $mod4);
    }
    $data   = base64_decode($data);
    $expire = substr($data,0,10);
    $data   = substr($data,10);

    if($expire > 0 && $expire < time()) {
        return '';
    }
    $x      = 0;
    $len    = strlen($data);
    $l      = strlen($key);
    $char   = $str = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }

    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1))<ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }else{
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

/**
 * 数据签名认证
 * @param  array  $data 被认证的数据
 * @return string       签名
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function data_auth_sign($data) {
    //数据类型检测
    if(!is_array($data)){
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

/**
* 对查询结果集进行排序
* @access public
* @param array $list 查询结果
* @param string $field 排序的字段名
* @param array $sortby 排序类型
* asc正向排序 desc逆向排序 nat自然排序
* @return array
*/
function list_sort_by($list,$field, $sortby='asc') {
   if(is_array($list)){
       $refer = $resultSet = array();
       foreach ($list as $i => $data)
           $refer[$i] = &$data[$field];
       switch ($sortby) {
           case 'asc': // 正向排序
                asort($refer);
                break;
           case 'desc':// 逆向排序
                arsort($refer);
                break;
           case 'nat': // 自然排序
                natcasesort($refer);
                break;
       }
       foreach ( $refer as $key=> $val)
           $resultSet[] = &$list[$key];
       return $resultSet;
   }
   return false;
}

/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId =  $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    //By.jingshuixian  如果tree为空 就返回默认数据
    return empty($tree)?$list:$tree;;
}

/**
 * 将list_to_tree的树还原成列表
 * @param  array $tree  原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array  $list  过渡用的中间数组，
 * @return array        返回排过序的列表数组
 * @author yangweijie <yangweijiester@gmail.com>
 */
function tree_to_list($tree, $child = '_child', $order='id', &$list = array()){
    if(is_array($tree)) {
        foreach ($tree as $key => $value) {
            $reffer = $value;
            if(isset($reffer[$child])){
                unset($reffer[$child]);
                tree_to_list($value[$child], $child, $order, $list);
            }
            $list[] = $reffer;
        }
        $list = list_sort_by($list, $order, $sortby='asc');
    }
    return $list;
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function format_bytes($size, $delimiter = '') {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 设置跳转页面URL
 * 使用函数再次封装，方便以后选择不同的存储方式（目前使用cookie存储）
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function set_redirect_url($url){
    cookie('redirect_url', $url);
}

/**
 * 获取跳转页面URL
 * @return string 跳转页URL
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_redirect_url(){
    $url = cookie('redirect_url');
    return empty($url) ? __APP__ : $url;
}

/**
 * 处理插件钩子
 * @param string $hook   钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook($hook,$params=array()){
    \Think\Hook::listen($hook,$params);
}

/**
 * 获取插件类的类名
 * @param strng $name 插件名
 */
function get_addon_class($name){
    $class = "Addons\\{$name}\\{$name}Addon";
    return $class;
}

/**
 * 获取插件类的配置文件数组
 * @param string $name 插件名
 */
function get_addon_config($name){
    $class = get_addon_class($name);
    if(class_exists($class)) {
        $addon = new $class();
        return $addon->getConfig();
    }else {
        return array();
    }
}

/**
 * 插件显示内容里生成访问插件的url
 * @param string $url url
 * @param array $param 参数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function addons_url($url, $param = array()){
    $url        = parse_url($url);
    $case       = C('URL_CASE_INSENSITIVE');
    $addons     = $case ? parse_name($url['scheme']) : $url['scheme'];
    $controller = $case ? parse_name($url['host']) : $url['host'];
    $action     = trim($case ? strtolower($url['path']) : $url['path'], '/');

    /* 解析URL带的参数 */
    if(isset($url['query'])){
        parse_str($url['query'], $query);
        $param = array_merge($query, $param);
    }

    /* 基础参数 */
    $params = array(
        '_addons'     => $addons,
        '_controller' => $controller,
        '_action'     => $action,
    );
    $params = array_merge($params, $param); //添加额外参数

    return U('Addons/execute', $params);
}

/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 * @author huajie <banhuajie@163.com>
 */
function time_format($time = NULL,$format='Y-m-d H:i'){
    $time = $time === NULL ? NOW_TIME : intval($time);
    return date($format, $time);
}

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
 * 获取分类信息并缓存分类
 * @param  integer $id    分类ID
 * @param  string  $field 要获取的字段名
 * @return string         分类信息
 */
function get_category($id, $field = null){
    static $list;

    /* 非法分类ID */
    if(empty($id) || !is_numeric($id)){
        return '';
    }

    /* 读取缓存数据 */
    if(empty($list)){
        $list = S('sys_category_list');
    }

    /* 获取分类名称 */
    if(!isset($list[$id])){
        $cate = M('Category')->find($id);
        if(!$cate || 1 != $cate['status']){ //不存在分类，或分类被禁用
            return '';
        }
        $list[$id] = $cate;
        S('sys_category_list', $list); //更新缓存
    }
    return is_null($field) ? $list[$id] : $list[$id][$field];
}

/* 根据ID获取分类标识 */
function get_category_name($id){
    return get_category($id, 'name');
}

/* 根据ID获取分类名称 */
function get_category_title($id){
    return get_category($id, 'title');
}

/**
 * 获取顶级模型信息
 */
function get_top_model($model_id=null){
    $map   = array('status' => 1, 'extend' => 0);
    if(!is_null($model_id)){
        $map['id']  =   array('neq',$model_id);
    }
    $model = M('Model')->where($map)->field(true)->select();
    foreach ($model as $value) {
        $list[$value['id']] = $value;
    }
    return $list;
}

/**
 * 获取文档模型信息
 * @param  integer $id    模型ID
 * @param  string  $field 模型字段
 * @return array
 */
function get_document_model($id = null, $field = null){
    static $list;

    /* 非法分类ID */
    if(!(is_numeric($id) || is_null($id))){
        return '';
    }

    /* 读取缓存数据 */
    if(empty($list)){
        $list = S('DOCUMENT_MODEL_LIST');
    }

    /* 获取模型名称 */
    if(empty($list)){
        $map   = array('status' => 1, 'extend' => 1);
        $model = M('Model')->where($map)->field(true)->select();
        foreach ($model as $value) {
            $list[$value['id']] = $value;
        }
        S('DOCUMENT_MODEL_LIST', $list); //更新缓存
    }

    /* 根据条件返回数据 */
    if(is_null($id)){
        return $list;
    } elseif(is_null($field)){
        return $list[$id];
    } else {
        return $list[$id][$field];
    }
}

/**
 * 解析UBB数据
 * @param string $data UBB字符串
 * @return string 解析为HTML的数据
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function ubb($data){
    //TODO: 待完善，目前返回原始数据
    return $data;
}

/**
 * 记录行为日志，并执行该行为的规则
 * @param string $action 行为标识
 * @param string $model 触发行为的模型名
 * @param int $record_id 触发行为的记录id
 * @param int $user_id 执行行为的用户id
 * @return boolean
 * @author huajie <banhuajie@163.com>
 */
function action_log($action = null, $model = null, $record_id = null, $user_id = null){

    //参数检查
    if(empty($action) || empty($model) || empty($record_id)){
        return '参数不能为空';
    }
    if(empty($user_id)){
        $user_id = is_login();
    }

    //查询行为,判断是否执行
    $action_info = M('Action')->getByName($action);
    if($action_info['status'] != 1){
        return '该行为被禁用或删除';
    }

    //插入行为日志
    $data['action_id']      =   $action_info['id'];
    $data['user_id']        =   $user_id;
    $data['action_ip']      =   ip2long(get_client_ip());
    $data['model']          =   $model;
    $data['record_id']      =   $record_id;
    $data['create_time']    =   NOW_TIME;

    //解析日志规则,生成日志备注
    if(!empty($action_info['log'])){
        if(preg_match_all('/\[(\S+?)\]/', $action_info['log'], $match)){
            $log['user']    =   $user_id;
            $log['record']  =   $record_id;
            $log['model']   =   $model;
            $log['time']    =   NOW_TIME;
            $log['data']    =   array('user'=>$user_id,'model'=>$model,'record'=>$record_id,'time'=>NOW_TIME);
            foreach ($match[1] as $value){
                $param = explode('|', $value);
                if(isset($param[1])){
                    $replace[] = call_user_func($param[1],$log[$param[0]]);
                }else{
                    $replace[] = $log[$param[0]];
                }
            }
            $data['remark'] =   str_replace($match[0], $replace, $action_info['log']);
        }else{
            $data['remark'] =   $action_info['log'];
        }
    }else{
        //未定义日志规则，记录操作url
        $data['remark']     =   '操作url：'.$_SERVER['REQUEST_URI'];
    }

    M('ActionLog')->add($data);

    if(!empty($action_info['rule'])){
        //解析行为
        $rules = parse_action($action, $user_id);

        //执行行为
        $res = execute_action($rules, $action_info['id'], $user_id);
    }
}

/**
 * 解析行为规则
 * 规则定义  table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
 * 规则字段解释：table->要操作的数据表，不需要加表前缀；
 *              field->要操作的字段；
 *              condition->操作的条件，目前支持字符串，默认变量{$self}为执行行为的用户
 *              rule->对字段进行的具体操作，目前支持四则混合运算，如：1+score*2/2-3
 *              cycle->执行周期，单位（小时），表示$cycle小时内最多执行$max次
 *              max->单个周期内的最大执行次数（$cycle和$max必须同时定义，否则无效）
 * 单个行为后可加 ； 连接其他规则
 * @param string $action 行为id或者name
 * @param int $self 替换规则里的变量为执行用户的id
 * @return boolean|array: false解析出错 ， 成功返回规则数组
 * @author huajie <banhuajie@163.com>
 */
function parse_action($action , $self){
    if(empty($action)){
        return false;
    }

    //参数支持id或者name
    if(is_numeric($action)){
        $map = array('id'=>$action);
    }else{
        $map = array('name'=>$action);
    }

    //查询行为信息
    $info = M('Action')->where($map)->find();
    if(!$info || $info['status'] != 1){
        return false;
    }

    //解析规则:table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
    $rules = $info['rule'];
    $rules = str_replace('{$self}', $self, $rules);
    $rules = explode(';', $rules);
    $return = array();
    foreach ($rules as $key=>&$rule){
        $rule = explode('|', $rule);
        foreach ($rule as $k=>$fields){
            $field = empty($fields) ? array() : explode(':', $fields);
            if(!empty($field)){
                $return[$key][$field[0]] = $field[1];
            }
        }
        //cycle(检查周期)和max(周期内最大执行次数)必须同时存在，否则去掉这两个条件
        if(!array_key_exists('cycle', $return[$key]) || !array_key_exists('max', $return[$key])){
            unset($return[$key]['cycle'],$return[$key]['max']);
        }
    }

    return $return;
}

/**
 * 执行行为
 * @param array $rules 解析后的规则数组
 * @param int $action_id 行为id
 * @param array $user_id 执行的用户id
 * @return boolean false 失败 ， true 成功
 * @author huajie <banhuajie@163.com>
 */
function execute_action($rules = false, $action_id = null, $user_id = null){
    if(!$rules || empty($action_id) || empty($user_id)){
        return false;
    }

    $return = true;
    foreach ($rules as $rule){

        //检查执行周期
        $map = array('action_id'=>$action_id, 'user_id'=>$user_id);
        $map['create_time'] = array('gt', NOW_TIME - intval($rule['cycle']) * 3600);
        $exec_count = M('ActionLog')->where($map)->count();
        if($exec_count > $rule['max']){
            continue;
        }

        //执行数据库操作
        $Model = M(ucfirst($rule['table']));
        $field = $rule['field'];
        $res = $Model->where($rule['condition'])->setField($field, array('exp', $rule['rule']));

        if(!$res){
            $return = false;
        }
    }
    return $return;
}

//基于数组创建目录和文件
function create_dir_or_files($files){
    foreach ($files as $key => $value) {
        if(substr($value, -1) == '/'){
            mkdir($value);
        }else{
            @file_put_contents($value, '');
        }
    }
}

if(!function_exists('array_column')){
    function array_column(array $input, $columnKey, $indexKey = null) {
        $result = array();
        if (null === $indexKey) {
            if (null === $columnKey) {
                $result = array_values($input);
            } else {
                foreach ($input as $row) {
                    $result[] = $row[$columnKey];
                }
            }
        } else {
            if (null === $columnKey) {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row;
                }
            } else {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row[$columnKey];
                }
            }
        }
        return $result;
    }
}

/**
 * 获取表名（不含表前缀）
 * @param string $model_id
 * @return string 表名
 * @author huajie <banhuajie@163.com>
 */
function get_table_name($model_id = null){
    if(empty($model_id)){
        return false;
    }
    $Model = M('Model');
    $name = '';
    $info = $Model->getById($model_id);
    if($info['extend'] != 0){
        $name = $Model->getFieldById($info['extend'], 'name').'_';
    }
    $name .= $info['name'];
    return $name;
}

/**
 * 获取属性信息并缓存
 * @param  integer $id    属性ID
 * @param  string  $field 要获取的字段名
 * @return string         属性信息
 */
function get_model_attribute($model_id, $group = true,$fields=true){
    static $list;

    /* 非法ID */
    if(empty($model_id) || !is_numeric($model_id)){
        return '';
    }

    /* 获取属性 */
    if(!isset($list[$model_id])){
        $map = array('model_id'=>$model_id);
        $extend = M('Model')->getFieldById($model_id,'extend');

        if($extend){
            $map = array('model_id'=> array("in", array($model_id, $extend)));
        }
        $info = M('Attribute')->where($map)->field($fields)->select();
        $list[$model_id] = $info;
    }

    $attr = array();
    if($group){
        foreach ($list[$model_id] as $value) {
            $attr[$value['id']] = $value;
        }
        $model     = M("Model")->field("field_sort,attribute_list,attribute_alias")->find($model_id);
        $attribute = explode(",", $model['attribute_list']);
        if (empty($model['field_sort'])) { //未排序
            $group = array(1 => array_merge($attr));
        } else {
            $group = json_decode($model['field_sort'], true);

            $keys = array_keys($group);
            foreach ($group as &$value) {
                foreach ($value as $key => $val) {
                    $value[$key] = $attr[$val];
                    unset($attr[$val]);
                }
            }

            if (!empty($attr)) {
                foreach ($attr as $key => $val) {
                    if (!in_array($val['id'], $attribute)) {
                        unset($attr[$key]);
                    }
                }
                $group[$keys[0]] = array_merge($group[$keys[0]], $attr);
            }
        }
        if (!empty($model['attribute_alias'])) {
            $alias  = preg_split('/[;\r\n]+/s', $model['attribute_alias']);
            $fields = array();
            foreach ($alias as &$value) {
                $val             = explode(':', $value);
                $fields[$val[0]] = $val[1];
            }
            foreach ($group as &$value) {
                foreach ($value as $key => $val) {
                    if (!empty($fields[$val['name']])) {
                        $value[$key]['title'] = $fields[$val['name']];
                    }
                }
            }
        }
        $attr = $group;
    }else{
        foreach ($list[$model_id] as $value) {
            $attr[$value['name']] = $value;
        }
    }
    return $attr;
}

/**
 * 调用系统的API接口方法（静态方法）
 * api('User/getName','id=5'); 调用公共模块的User接口的getName方法
 * api('Admin/User/getName','id=5');  调用Admin模块的User接口
 * @param  string  $name 格式 [模块名]/接口名/方法名
 * @param  array|string  $vars 参数
 */
function api($name,$vars=array()){
    $array     = explode('/',$name);
    $method    = array_pop($array);
    $classname = array_pop($array);
    $module    = $array? array_pop($array) : 'Common';
    $callback  = $module.'\\Api\\'.$classname.'Api::'.$method;
    if(is_string($vars)) {
        parse_str($vars,$vars);
    }
    return call_user_func_array($callback,$vars);
}

/**
 * 根据条件字段获取指定表的数据
 * @param mixed $value 条件，可用常量或者数组
 * @param string $condition 条件字段
 * @param string $field 需要返回的字段，不传则返回整个数据
 * @param string $table 需要查询的表
 * @author huajie <banhuajie@163.com>
 */
function get_table_field($value = null, $condition = 'id', $field = null, $table = null){
    if(empty($value) || empty($table)){
        return false;
    }

    //拼接参数
    $map[$condition] = $value;
    $info = M(ucfirst($table))->where($map);
    if(empty($field)){
        $info = $info->field(true)->find();
    }else{
        $info = $info->getField($field);
    }
    return $info;
}

/**
 * 获取链接信息
 * @param int $link_id
 * @param string $field
 * @return 完整的链接信息或者某一字段
 * @author huajie <banhuajie@163.com>
 */
function get_link($link_id = null, $field = 'url'){
    $link = '';
    if(empty($link_id)){
        return $link;
    }
    $link = M('Url')->getById($link_id);
    if(empty($field)){
        return $link;
    }else{
        return $link[$field];
    }
}

/**
 * 获取文档封面图片
 * @param int $cover_id
 * @param string $field
 * @return 完整的数据  或者  指定的$field字段值
 * @author huajie <banhuajie@163.com>
 */
function get_cover($cover_id, $field = 'path',$return_no_pic = true){
    if(empty($cover_id)){
        //By.jingshuixian  为空  返回暂无图片
        if($return_no_pic){
            return C('NO_PIC');
        }
        return ;
    }
    $picture = M('Picture')->where(array('status'=>1))->getById($cover_id);
    if($field == 'path'){
        if(!empty($picture['url'])){
            $picture['path'] = $picture['url'];
        }else{
            $picture['path'] = __ROOT__.$picture['path'];
        }
    }

    return empty($field) ? $picture : $picture[$field];
}

/**
 * 检查$pos(推荐位的值)是否包含指定推荐位$contain
 * @param number $pos 推荐位的值
 * @param number $contain 指定推荐位
 * @return boolean true 包含 ， false 不包含
 * @author huajie <banhuajie@163.com>
 */
function check_document_position($pos = 0, $contain = 0){
    if(empty($pos) || empty($contain)){
        return false;
    }

    //将两个参数进行按位与运算，不为0则表示$contain属于$pos
    $res = $pos & $contain;
    if($res !== 0){
        return true;
    }else{
        return false;
    }
}

/**
 * 获取数据的所有子孙数据的id值
 * @author 朱亚杰 <xcoolcc@gmail.com>
 */

function get_stemma($pids,Model &$model, $field='id'){
    $collection = array();

    //非空判断
    if(empty($pids)){
        return $collection;
    }

    if( is_array($pids) ){
        $pids = trim(implode(',',$pids),',');
    }
    $result     = $model->field($field)->where(array('pid'=>array('IN',(string)$pids)))->select();
    $child_ids  = array_column ((array)$result,'id');

    while( !empty($child_ids) ){
        $collection = array_merge($collection,$result);
        $result     = $model->field($field)->where( array( 'pid'=>array( 'IN', $child_ids ) ) )->select();
        $child_ids  = array_column((array)$result,'id');
    }
    return $collection;
}

/**
 * 验证分类是否允许发布内容
 * @param  integer $id 分类ID
 * @return boolean     true-允许发布内容，false-不允许发布内容
 */
function check_category($id){
    if (is_array($id)) {
		$id['type']	=	!empty($id['type'])?$id['type']:2;
        $type = get_category($id['category_id'], 'type');
        $type = explode(",", $type);
        return in_array($id['type'], $type);
    } else {
        $publish = get_category($id, 'allow_publish');
        return $publish ? true : false;
    }
}

/**
 * 检测分类是否绑定了指定模型
 * @param  array $info 模型ID和分类ID数组
 * @return boolean     true-绑定了模型，false-未绑定模型
 */
function check_category_model($info){
    $cate   =   get_category($info['category_id']);
    $array  =   explode(',', $info['pid'] ? $cate['model_sub'] : $cate['model']);
    return in_array($info['model_id'], $array);
}


/**
 * 获得子分类
 * @param int $type
 * @param int $pid
 * @param int $moreLevel 最多显示几级
 * @param int $level  当前层级
 * @return mixed
 * @author By.jingshuixian
 */
function get_child_category($pid = 0,$moreLevel=3,$level = 0){
    if($level >= $moreLevel) return ;
    $where['pid']   =   $pid;
    $where['hide']  =   0;
    $menus = M('Category')->where($where)->order('sort asc')->field('id,title,name')->select();
    foreach ($menus as $key => $item) {
        $menus[$key]['level']  =    $level;
        $menus[$key]['_'] = get_child_category($item['id'],$moreLevel,$level+1);
    }
    return $menus ;
}

/**
 * 获得子分类
 * @param int $pid
 * @param bool $addself
 * @param int $moreLevel
 * @param int $level
 * @param array $ids
 * @return string|void
 */
function get_child_category_ids($pid = 0,$addself = false,$moreLevel=4,$level = 0,&$ids=array() ){

    if($level >= $moreLevel) return ;
    $where['pid']   =   $pid;
    $where['hide']  =   0;
    $menus = M('Category')->where($where)->order('sort asc')->field('id,title,name')->select();

    foreach ($menus as $key => $item) {
        $ids[] =$item['id'];
        get_child_category_ids($item['id'],$addself,$moreLevel,$level+1,$ids);
    }
    if($addself) $ids[] = $pid;
    trace($ids,'get_child_category_ids---'.$pid);
    return implode(',',$ids) ;
}

/**
 * 获取当前页面完整URL地址
 */
function get_url() {
    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
    return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
}


/**
 * 获取顶级分类
 * @param $id
 * @param bool $field
 * @return mixed
 * @author By.jingshuixian
 */
function getTopLevelCate($id, $field = true){
    $info = D('Category')->info($id, 'id');
    if($info['pid'] > 0){
        getTopLevelCate($info['pid']);
    }
    return $info;
}

/**
 * @param $id
 * @param string $field
 * @return mixed
 * @author By.jingshuixian 获取顶级分类ID
 */
function getTopLevelCateid($id, $field = 'id,pid'){
    $info = getTopLevelCate($id,$field);
    return $info['id'];
}

/**
 * @param $sourcestr
 * @param int $cutlength
 * @param string $dot
 * @return string
 * @author jingshuixian   来自phpokcms 的字符串截取方法
 * 说明:    两个英文按照一个中文来截取,比如  $str =  我爱think ; _substr($str,3,'');  结果为 : 我爱th
 */
function _substr($sourcestr,$cutlength=254,$dot='')
{
    $returnstr = '';
    $i = 0;
    $n = 0;
    $str_length = strlen($sourcestr);
    $mb_str_length = mb_strlen($sourcestr,'utf-8');
    while(($n < $cutlength) && ($i <= $str_length)){
        $temp_str = substr($sourcestr,$i,1);
        $ascnum = ord($temp_str);
        if($ascnum >= 224){
            $returnstr = $returnstr.substr($sourcestr,$i,3);
            $i = $i + 3;
            $n++;
        }elseif($ascnum >= 192){
            $returnstr = $returnstr.substr($sourcestr,$i,2);
            $i = $i + 2;
            $n++;
        }elseif(($ascnum >= 65) && ($ascnum <= 90)){
            $returnstr = $returnstr.substr($sourcestr,$i,1);
            $i = $i + 1;
            $n = $n + 0.5;
        }else{
            $returnstr = $returnstr.substr($sourcestr,$i,1);
            $i = $i + 1;
            $n = $n + 0.5;
        }
    }
    if ($mb_str_length > $cutlength){
        $returnstr = $returnstr . $dot;
    }
    return $returnstr;
}

function trimall($str)//删除空格
{
    $qian=array(" ","　","\t","\n","\r");$hou=array("","","","","");
    return str_replace($qian,$hou,$str);
}


/**
 * 格式化list里数据信息
 * @author By.jingshuixian
 */
function forma_list_data($list ,$type = 'cate',$attr = array()){

    foreach($list as $key => $item){

        $list[$key] = format_item_data($item,$type ,$attr);

    }

    return $list;
}

function format_item_data($item ,$type = 'cate',$attr = array()){

    if(empty($item)) return $item;


    $href = '';

    if($type == 'cate') {
        if ($item['type'] == 4) {

            //$href = get_nav_url('Article/page?category=' . $item['name']);
            $href = get_nav_url('page/' . (empty($item['name'])?$item['id']:$item['name']));
        } elseif ($item['type'] == 5) {
            $href = get_nav_url($item['url']);
        } else {
            //$href = get_nav_url('Article/lists?category=' . $item['name']);
            $href = get_nav_url('lists/' .  (empty($item['name'])?$item['id']:$item['name']));
        }
        $item['href']  = $href;

        //格式化 缩略图

        $item['icon'] = get_cover($item['icon']);

    }elseif($type == 'arc'){
       // var_dump($item);
       //$href = get_nav_url('Article/detail?id=' . (empty($item['name'])?$item['id']:$item['name'] ));
        $href = get_nav_url('detail/' . (empty($item['name'])?$item['id']:$item['name']));

        $item['href']  = $href;

        //$item['cover'] =C('WEB_SITE_URL') . get_cover( $item['cover_id'],'path');
        $item['cover'] = get_cover( $item['cover_id'],'path');
        $item['icon'] = get_cover($item['cover_id']);
    }else{

        foreach($attr as $key => $val){

            if($key=='img'){
                $item[$val] = get_cover($item[$val]);
            }
        }


    }

    return $item;
}

/**
 * 格式化配置,用来格式三大标签的seo的配置
 */
function format_config($str='',$addfind =array(),$addreplace=array()){
    $find = array(
        '%WEB_SITE_TITLE%',
        '%WEB_SITE_URL%'
        );

    $replace = array(C('WEB_SITE_TITLE'),C('WEB_SITE_URL'));


    $str = str_replace($find,$replace,$str);
    $str = str_replace($addfind,$addreplace,$str);

    return $str;
}

/**
 * 获取导航URL
 * @param  string $url 导航URL
 * @return string      解析或的url
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_nav_url($url){
    switch ($url) {
        case 'http://' === substr($url, 0, 7):
        case '#' === substr($url, 0, 1):
            break;
        default:
            $url = U($url);
            break;
    }
    return $url;
}


function ints_to_string(&$data,$map=array('status'=>array(1=>'正常',-1=>'删除',0=>'禁用',2=>'未审核',3=>'草稿'))) {
    if($data === false || $data === null ){
        return $data;
    }
    $data = (array)$data;
    foreach ($data as $key => $row){
        foreach ($map as $col=>$pair){

            //$col 是  key
            //var_dump($col);
            if(isset($row[$col]) ){ //&& isset($pair[$row[$col]])
                //var_dump($pair);

                $str = $row[$col];
                $strarr = str2arr($str);
                //var_dump($strarr);

                $retstr = array();
                foreach($strarr as $sk => $sval){
                    //var_dump($sval);
                    if(isset($pair[$sval])){
                        $retstr[] =  $pair[$sval];
                    }
                }
                //var_dump($retstr);
                $data[$key][$col.'_text'] = arr2str($retstr);
            }
        }
    }
    return $data;
}


/**
 * 获取当前位置栏目数组
 * @param $id 栏目id  (categoryid)
 * @param $pos_list 返回 列表
 * @return mixed
 * @author jingshuixian  294721288@qq.com
 */
function got_pos($id,&$pos_list){
    $category =  M('Category')->find($id);

    array_unshift ($pos_list,$category);
    if($category['pid'] > 0){
        //echo $category['pid'];
        got_pos($category['pid'],$pos_list);
    }

    return $pos_list;
}


function read_json_ini($filename = ""){
    if(empty($filename)){
        $filename =  $_SERVER['DOCUMENT_ROOT'] .'/Templates/' .C('WEB_DEFAULT_THEME').'/config.ini';
    }
    $handle = fopen($filename, "r");//读取二进制文件时，需要将第二个参数设置成'rb'
    //通过filesize获得文件大小，将整个文件一下子读到一个字符串中
    $contents = fread($handle, filesize ($filename));
    fclose($handle);
    return json_decode($contents,true);
}

/**
 * 获得LOGO
 * @return 完整的数据
 */
function get_logo(){
    return get_cover(C('WEB_LOGO'),'path');
}
function get_erweima(){
    return get_cover(C('COM_ERWEIMA'),'path');
}

/**
 * @param array $seting
 * 获得播放器
 */
function get_player($seting=array()){

        $seting['width'] = empty($seting['width']) ? 280 : $seting['width'];
        $seting['height'] = empty($seting['height']) ? 170 : $seting['height'];
        
        $seting['CuPlayerSetFile'] = empty($seting['CuPlayerSetFile']) ? "/Public/static/CuPlayer/CuPlayerSetFile.xml" : $seting['CuPlayerSetFile'];
        $seting['CuPlayerFile'] = empty($seting['CuPlayerFile']) ? "/Public/static/CuPlayer/test.flv" : $seting['CuPlayerFile'];

        $seting['CuPlayerImage'] = empty($seting['CuPlayerImage']) ? "/Public/static/CuPlayer/images/start.jpg" : $seting['CuPlayerImage'];

        $seting['CuPlayerWidth'] = empty($seting['CuPlayerWidth']) ? 289 : $seting['CuPlayerWidth'];
        $seting['CuPlayerHeight'] = empty($seting['CuPlayerHeight']) ? 160 : $seting['CuPlayerHeight'];

        //播放器容器ID
        $seting['id'] = empty($seting['id']) ? "CuPlayer" : $seting['id'];

        $return = 
        '<script type="text/javascript" src="/Public/static/CuPlayer/images/swfobject.js"></script>'
        .'<script type="text/javascript">'
        .'var so = new SWFObject("/Public/static/CuPlayer/CuPlayerMiniV4.swf","CuPlayerV4","'.$seting['width'].'","'.$seting['height'].'","9","#000000");'
        .'so.addParam("allowfullscreen","true");'
        .'so.addParam("allowscriptaccess","always");'
        .'so.addParam("wmode","opaque");'
        .'so.addParam("quality","high");'
        .'so.addParam("salign","lt");'
        .'so.addVariable("CuPlayerSetFile","'.$seting['CuPlayerSetFile'].'");' //播放器配置文件地址
        .'so.addVariable("CuPlayerFile","'.$seting['CuPlayerFile'].'");' //视频文件地址
        .'so.addVariable("CuPlayerImage","'.$seting['CuPlayerImage'].'");'//视频略缩图
        .'so.addVariable("CuPlayerWidth","'.$seting['CuPlayerWidth'].'"); '//视频宽度
        .'so.addVariable("CuPlayerHeight","'.$seting['CuPlayerHeight'].'");' //视频高度
        .'so.addVariable("CuPlayerAutoPlay","no"); '//是否自动播放
        .'so.write("'.$seting['id'].'");'
        .'</script>';


        echo $return;
}

/**
 * @param int $category_id
 * @param int $model_id
 * @return array
 * @获取分类的筛选信息
 */
function get_filter($category_id=0 , $model_id =0 ){
    $depr = C('URL_PATHINFO_DEPR');
    $returnArray = array();

    $attr = get_filter_attr($model_id);


    foreach($attr as $key=>$value){
        $returnArrayOne = array();
        $links = array();

        $name = $value['name'];
        $title =  $value['title'];
        $extra = $value['extra'];
        $is_filter = $value['is_filter'];
        $val = I($name);
        $val = empty($val) ? "0": $val;

        //当前字段的链接地址
        //$attrurl = "/lists/$category_id.html?";
        $attrurl = array();
        $attrurl[] = '/lists';
        $attrurl[] = $category_id;

        //获取除了本身参数以为其他参数的集合,组成链接
        foreach($attr as $keys=>$vals) {
            if($name !=$vals['name'] ){
                //$attrurl .=$vals['name'].'='.I($vals['name']).'&';
                $attrurl[] = $vals['name'];
                $va = I($vals['name']);
                $va = empty($va) ? "0": $va;
                $attrurl[] = $va ;
            }
        }
        $attrurl = implode($depr,$attrurl);
        //判断是否给全部加样式
        $link['title']='全部';
        $link['class']= empty($val)?'current':'';  //on
        $link['href'] =  U($attrurl.$depr.$name.$depr.'0');

        $links[] = $link;
        //选项内容
        $items  = parse_field_attr($extra);
        foreach($items as $k=>$v){
            $link = array();
            $link['title'] = $v;

            if($val == $k ){
                $link['class'] = 'current'; // on
            }
            //$link['href'] = $attrurl.$name.'='.$k;
            $link['href'] =  U($attrurl.$depr.$name.$depr.$k);
            $links[] = $link;

        }

        $returnArrayOne['title'] = $title;
        $returnArrayOne['_'] = $links;

        $returnArray[] = $returnArrayOne;


    }
    //var_dump($returnArray);
    return $returnArray;
}
function get_filter_attr($model_id = 0){
    $where['is_filter'] = array("GT",0);
    $where['model_id'] = array("EQ", $model_id);
    $files='id,name,title,extra,is_filter';
    $attr = M('attribute')->where($where)->field($files)->select();
    return $attr;
}

function get_filter_maps($Attrs = Array()){
    $map = Array();
    foreach($Attrs as $key=>$value){
        $name = $value['name'];
        $is_filter = $value['is_filter'];

        $data = I($name);
        if(!empty($data)) {
            if ($is_filter == 2) {  //范文查询  大于最小值   小于最大值
                if (strpos($data, '-') > 0) {
                    $kkArr = explode('-', $data);
                    if (count($kkArr) == 2) {
                        $map[$name] = array('BETWEEN', $kkArr[0] . ',' . $kkArr[1]);
                    }
                }
            }else{   //其他查询
                $map[$name] = array('EQ', $data);
            }
        }
    }
    return $map;

}
//来自后台公用方法
// 分析枚举类型字段值 格式 a:名称1,b:名称2
// 暂时和 parse_config_attr功能相同
// 但请不要互相使用，后期会调整
function parse_field_attr($string) {
    if(0 === strpos($string,':')){
        // 采用函数定义
        return   eval('return '.substr($string,1).';');
    }elseif(0 === strpos($string,'[')){
        // 支持读取配置参数（必须是数组类型）
        return C(substr($string,1,-1));
    }

    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if(strpos($string,':')){
        $value  =   array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k]   = $v;
        }
    }else{
        $value  =   $array;
    }
    return $value;
}

// 获取模型标识 来自admin function
function get_model_name_by_id($id){
    return $model = M('Model')->getFieldById($id,'name');
}

/**
 * @param $model_id
 * @param string $filed
 * @param string $data
 * @return string
 * 获得枚举的值
 */
function get_em($model_id ,$filed ='', $data=''){
    $return = array();

    $where['model_id'] = array("EQ", $model_id);
    $where['name'] = array("EQ", $filed);
    $files='id,name,title,extra,is_filter';
    $attr = M('attribute')->where($where)->field($files)->find();

    if(!empty($attr) and !empty($data)){
        $str = explode(',',$data);
        $extra = $attr['extra'];
        $items = parse_field_attr($extra);

        foreach($str as $value){
            if(!empty($items[$value])){
                $return[] = $items[$value];
            }
        }

        return implode(',',$return);

    }

}

/**
 * @param $id
 * @return mixed
 * 获取文档的标题
 */
function get_doc_title($id){
    return M('document')->getFieldById($id,'title');
}


function get_model_is_list_by_id($id ){
    $where['model_id'] = $id;
    $where['is_list']  = 1;

    $field = 'name';

    $data = M('Attribute')->where($where)->field($field)->select();
    $return = array();
    foreach($data as $item){
        $return[] = $item['name'];
    }

    $data =  implode(',',$return);
    //var_dump($data);
    return $data ;
}

function get_now_mouth(){

    $data_begin = mktime(0, 0 , 0,date("m"),date("d"),date("Y"));
    $data_end =  mktime(23,59,59,date("m"),date("t"),date("Y"));

    //echo date("Y-m-d H:i:s",$data_begin);
    return   " $data_begin <= shijian  AND   shijian <= $data_end ";


}

function get_next_mouth(){
    $arr =  GetNextMonth( date("y-m-d"));
    $data_begin = strtotime($arr[0]);
    $data_end = strtotime($arr[1]);
    return   " $data_begin <= shijian  AND   shijian <= $data_end ";
}



function GetTheMonth($date){//获取指定日期所在月的第一天和最后一天
    $firstday = date("Y-m-01",strtotime($date));
    $lastday = date("Y-m-d",strtotime("$firstday +1 month -1 day"));
    return array($firstday,$lastday);
}
function GetPurMonth($date){//获取指定日期上个月的第一天和最后一天
    $time=strtotime($date);
    $firstday=date('Y-m-01',strtotime(date('Y',$time).'-'.(date('m',$time)-1).'-01'));
    $lastday=date('Y-m-d',strtotime("$firstday +1 month -1 day"));
    return array($firstday,$lastday);
}
function GetNextMonth($date){//获取指定日期下个月的第一天和最后一天

    $arr = getdate();
    if($arr['mon'] == 12){
        $year = $arr['year'] +1;
        $month = $arr['mon'] -11;
        $day = $arr['mday'];
        if($day < 10){
            $mday = '0'.$day;
        }else {
            $mday = $day;
        }
        $firstday = $year.'-0'.$month.'-01';
        $lastday = $year.'-0'.$month.'-'.$mday;
    }else{
        $time=strtotime($date);
        $firstday=date('Y-m-01',strtotime(date('Y',$time).'-'.(date('m',$time)+1).'-01'));
        $lastday=date('Y-m-d',strtotime("$firstday +1 month -1 day"));
    }
    return array($firstday,$lastday);
}

function get_next_month($date){
    $arr = getdate();
    if($arr['mon'] == 12){
        $month = $arr['mon'] -11;
        $firstday = $month;

    }else{
        $time=strtotime($date);
        $firstday=date('m',strtotime(date('Y',$time).'-'.(date('m',$time)+1).'-01'));

    }
    return $firstday;
}

/**
 * 查询 xiaobing360_file 表 文件后缀
 */
function get_file_ext($id){
    return M('file')->getFieldById($id,'ext');
}

/**
 *
 */
function get_file_view($id){
    $width = 825;
    $height = 535;
    $picExt = array('png','jpg',"gif");
    $voidExt = array('flv','mp4');
    $jqMedia = array('pdf','html','swf');
    $officeExt = array('doc','docx','ppt','pptx','xls','xlsx');

    $file = M('file')->find($id);
    $ext = $file['ext'];
    $path = C("DOWNLOAD_UPLOAD.rootPath").$file['savepath'].$file['savename'];
    $pathPage = C("DOWNLOAD_UPLOAD.imgPath").$file['savepath'].$file['savename'];

    if($ext == 'txt' || ext == 'xml'){ //文本  xml
        //$content = readfile($path);
        //$myfile = fopen($path, "r") or die("Unable to open file!");
        //$content =  fread($myfile,filesize($path));
        //fclose($myfile);

        $content = file_get_contents($path);
        $content = mb_convert_encoding($content, 'UTF-8','ascii,GB2312,gbk,UTF-8');

        $ret = "<textarea style='width: 100%; height: 535px;'   >$content</textarea>";
    }elseif( in_array($ext,$picExt)){ // 图片

        $ret = "<img src='$pathPage' width='100%'>";

    }elseif(in_array($ext,$voidExt)){  //视频
        $CuPlayerImage = "/Templates/weier/Static/images/start.png";
        echo '<div id="CuPlayer"></div>';

        $ret = get_player(array('CuPlayerFile' => $pathPage,"width"=>$width,"height"=>$height,'CuPlayerWidth'=>$width,'CuPlayerHeight'=>$height,'CuPlayerImage'=>$CuPlayerImage )) ;

        //$ret = '<a href="'.$pathPage.'" class="media"></a>';
        //$ret .= "<script>$('a.media').media({width:$width, height:$height});</script>";

    }elseif(in_array($ext,$jqMedia) or in_array($ext,$officeExt)){  // jQuery 插件支持的格式

        if(in_array($ext,$officeExt)){

            $pdfName =  basename($pathPage,$ext).'pdf';

            $outPath =  C("DOWNLOAD_UPLOAD.imgPath").$file['savepath'].$pdfName;
            $root = $_SERVER['DOCUMENT_ROOT'];
            if(!is_file($root.$outPath)){
               //exec("$root/to.vbs  $ext   $root$pathPage   $root$outPath");
                exec("$root/wps2pdf.exe  $ext   $root$pathPage   $root$outPath");

            }

            $pathPage = $outPath ;

        }

        $ret = '<a class="media" href="'.$pathPage.'"></a>';
        $ret .= "<script>$('a.media').media({width:$width, height:$height});</script>";
    }



    return $ret;

}



/////////////////////////////////////////////file/////////////////////////////////////


define ('UTF32_BIG_ENDIAN_BOM'   , chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF));
define ('UTF32_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00));
define ('UTF16_BIG_ENDIAN_BOM'   , chr(0xFE) . chr(0xFF));
define ('UTF16_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE));
define ('UTF8_BOM'               , chr(0xEF) . chr(0xBB) . chr(0xBF));

function detect_utf_encoding($text) {
    $first2 = substr($text, 0, 2);
    $first3 = substr($text, 0, 3);
    $first4 = substr($text, 0, 3);

    if ($first3 == UTF8_BOM) return 'UTF-8';
    elseif ($first4 == UTF32_BIG_ENDIAN_BOM) return 'UTF-32BE';
    elseif ($first4 == UTF32_LITTLE_ENDIAN_BOM) return 'UTF-32LE';
    elseif ($first2 == UTF16_BIG_ENDIAN_BOM) return 'UTF-16BE';
    elseif ($first2 == UTF16_LITTLE_ENDIAN_BOM) return 'UTF-16LE';
}
function getFileEncoding($str){
    $encoding=mb_detect_encoding($str);
    if(empty($encoding)){
        $encoding=detect_utf_encoding($str);
    }
    return $encoding;
}