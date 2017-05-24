<?php


/**
 * 获取目录的结构
 * @author 李俊
 * @param  [string] $path [目录路径]
 * @return [array]       [目录结构数组]
 */
function dirtree($path) {
    $handle = opendir($path);
    $itemArray=array();
    while (false !== ($file = readdir($handle))) {
        if (($file=='.')||($file=='..')){
        }elseif (is_dir($path.$file)) {
            try {
                $dirtmparr=dirtree($path.$file.'/');
            } catch (Exception $e) {
                $dirtmparr=null;
            };
            $itemArray[$file]=$dirtmparr;
        }else{
            array_push($itemArray, $file);
        }
    }
    return $itemArray;
}

/**
 * 获取目录结构列表
 * @param string $path
 * @return mixed
 */
function get_dir_list($path = LONG_WEB_SITE_TMPL_PATH){
    $dir = dirtree($path);
    foreach($dir as $val ){
        $return[$val] =$val;
    }
    return $return;
}

/**
 * 获得模板绑定配置信息
 * @param string $filename
 * @return mixed
 */
function read_json_ini($filename = ""){
    if(empty($filename)){
        $filename =  LONG_WEB_SITE_TMPL_PATH .'/' .C('WEB_DEFAULT_THEME').'/config.ini';
    }
    $handle = fopen($filename, "r");//读取二进制文件时，需要将第二个参数设置成'rb'
    //通过filesize获得文件大小，将整个文件一下子读到一个字符串中
    $contents = fread($handle, filesize ($filename));
    fclose($handle);
    return json_decode($contents,true);
}

/**
 * 给资源添加站点路径
 */

function add_website_path($data,$field,$setting = null){
    if(!is_array($field)) $field = str2arr($field);

    foreach($field as $val){
        if(empty($setting)){
            $path = substr(LONG_WEB_SITE_PATH,1);
        }else{
            $path =  substr($setting['sitePath'], 1);
        }
        if(is_array($data)){
            foreach($data as $key=>$item){
                //download
                $data[$key][$val] = $path. $data[$key][$val];

            }
        }else{
            $data = $path . $data;
        }
    }

    return $data;
}

/**
 * 获取枚举字段 关联数据
 * @param int $model_id
 * @param string $field
 * @分类ID  获取自定分类
 * @return mixed
 */
function get_docment_select($model_id = 0,$field = 'id,title',$cateid = 0){
    $where['model_id'] = $model_id;
    $where['status']  = 1;
    $field_arr = str2arr($field);
    if(!empty($cateid)){
        $where['category_id']  = $cateid;
    }

    $data = M('Document')->where($where)->field($field)->select();
    foreach($data as $key=> $value){
        $list[$value[$field_arr[0]]] = $value[$field_arr[1]];
    }
    return $list;
}

/**
 * 获取栏目的列表
 * @param int $pid
 * @return mixed
 */
function get_category_select($pid = 0){
    $field = 'id,title';
    $field_arr = str2arr($field);
    if(!empty($pid)){
        $where['pid']  = $pid;
    }
    $data = M('Category')->where($where)->field($field)->select();
    foreach($data as $key=> $value){
        $list[$value[$field_arr[0]]] = $value[$field_arr[1]];
    }
    return $list;
}

/**
 * 获取枚举字段 获取用户
 * @param int $model_id
 * @param string $field
 * @return mixed
 */
function get_user_list($field = 'uid,nickname'){
    $where['status']  = 1;
    $field_arr = str2arr($field);

    $data = M('Member')->where($where)->field($field)->select();
    foreach($data as $key=> $value){
        $list[$value[$field_arr[0]]] = $value[$field_arr[1]];
    }
    return $list;
}

/**
 * 获得配置值
 * @param $configName  配置名称
 * @param $key  主键
 * @param $defaultValue 默认值
 */
function get_config_value($configName,$key="",$defaultValue=""){

    $M = M('config');
    $config  = $M->where(array('name'=>$configName))->find();

    if($config){
        $value =  C($configName);
        switch($config['type']){
            case '5':
                $value = get_cover($value);
                break ;
            case '3':
                $value = $key === "" ? $value :  $value[$key];
                break;
            default:

        }

    }

    $value = empty($value) ? $defaultValue:$value ;


    return $value;
}

/**
 * By.jingshuixian
 * 检查当前提交的数组选择是否选中
 * @param $val
 * @return bool
 */
function check_field_checked($val){
    if(is_array($val) && count($val) > 0){
        return true ;
    }
    return false;
}

/**
 * 根据图片ID 获取图片路径
 * 对 get_cover 方法进行名称简化包装
 * @param $id
 * By.jingshuixian  2016年3月31日 23:33:29
 */
function get_img($cover_id){
    return get_cover($cover_id, $field = 'path',$return_no_pic = true);
}

function get_ico(){
	
	$cover_id = C('WEB_FAVICON');
	
	if($cover_id){
		$path = get_cover($cover_id, $field = 'path',$return_no_pic = true);
	}else{
        $path = '/favicon.ico';
    }

    $return = '<link href="'. $path .'" rel="bookmark" type="image/x-icon" /> ';
    $return .= '<link href="'. $path .'" rel="icon" type="image/x-icon" />  ';
    $return .= '<link href="'. $path .'" rel="shortcut icon" type="image/x-icon" />  ';
    $return .= '<meta name="author" content="成都网站建设龙兵科技 http://www.xbjianzhan.com" />';
	

	return $return;

}

/**
 * 微信通知接口
 * @param string $name
 * @param string $content
 * @return bool
 */
function sendWX($key , $name="",$content=""){

    $tmpl = C("TONGZHI_WX_TMPL");
    $longid = C("TONGZHI_WX_LONGID");

    if(empty($key) || empty($tmpl) || empty($longid))  return ;

    $url = "http://cyq.365bole.cn/web/index.php?c=site&a=entry&eid=35";
    $post  = array('key'=>$key, 'name'=>$name,"content"=>$content,"tmpl" => $tmpl,"longid"=> $longid);
    curl_post($url,$post);

}

/**
 * 模拟post 提交数据
 * @param $url
 * @param $post
 * @return mixed
 */
function curl_post($url, $post) {
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $post,
    );

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

/**
 * 数字转中文
 * @param $num
 * @param int $m
 * @return string
 */
function number2Chinese($num, $m = 1) {
    switch($m) {
        case 0:
            $CNum = array(
                array('零','壹','贰','叁','肆','伍','陆','柒','捌','玖'),
                array('','拾','佰','仟'),
                array('','萬','億','萬億')
            );
            break;
        default:
            $CNum = array(
                array('零','一','二','三','四','五','六','七','八','九'),
                array('','十','百','千'),
                array('','万','亿','万亿')
            );
            break;
    }
// $cNum = array('零','一','二','三','四','五','六','七','八','九');

    if (is_integer($num)) {
        $int = (string)$num;
    } else if (is_numeric($num)) {
        $num = explode('.', (string)floatval($num));
        $int = $num[0];
        $fl  = isset($num[1]) ? $num[1] : FALSE;
    }
// 长度
    $len = strlen($int);
// 中文
    $chinese = array();

// 反转的数字
    $str = strrev($int);
    for($i = 0; $i<$len; $i+=4 ) {
        $s = array(0=>$str[$i], 1=>$str[$i+1], 2=>$str[$i+2], 3=>$str[$i+3]);
        $j = '';
// 千位
        if ($s[3] !== '') {
            $s[3] = (int) $s[3];
            if ($s[3] !== 0) {
                $j .= $CNum[0][$s[3]].$CNum[1][3];
            } else {
                if ($s[2] != 0 || $s[1] != 0 || $s[0]!=0) {
                    $j .= $CNum[0][0];
                }
            }
        }
// 百位
        if ($s[2] !== '') {
            $s[2] = (int) $s[2];
            if ($s[2] !== 0) {
                $j .= $CNum[0][$s[2]].$CNum[1][2];
            } else {
                if ($s[3]!=0 && ($s[1] != 0 || $s[0]!=0) ) {
                    $j .= $CNum[0][0];
                }
            }
        }
// 十位
        if ($s[1] !== '') {
            $s[1] = (int) $s[1];
            if ($s[1] !== 0) {
                $j .= $CNum[0][$s[1]].$CNum[1][1];
            } else {
                if ($s[0]!=0 && $s[2] != 0) {
                    $j .= $CNum[0][$s[1]];
                }
            }
        }
// 个位
        if ($s[0] !== '') {
            $s[0] = (int) $s[0];
            if ($s[0] !== 0) {
                $j .= $CNum[0][$s[0]].$CNum[1][0];
            } else {
// $j .= $CNum[0][0];
            }
        }
        $j.=$CNum[2][$i/4];
        array_unshift($chinese, $j);
    }
    $chs = implode('', $chinese);
    if ($fl) {
        $chs .= '点';
        for($i=0,$j=strlen($fl); $i<$j; $i++) {
            $t = (int)$fl[$i];
            $chs.= $str[0][$t];
        }
    }
    return $chs;
}

/**
 * 时间差
 * @param $begin_time
 * @param $end_time
 * @return array
 */
function timediff( $begin_time, $end_time )
{
    if ( $begin_time < $end_time ) {
        $starttime = $begin_time;
        $endtime = $end_time;
    } else {
        $starttime = $end_time;
        $endtime = $begin_time;
    }
    $timediff = $endtime - $starttime;
    $days = intval( $timediff / 86400 );
    $remain = $timediff % 86400;
    $hours = intval( $remain / 3600 );
    $remain = $remain % 3600;
    $mins = intval( $remain / 60 );
    $secs = $remain % 60;
    $res = array( "day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs );
    return $res;
}

function get_time_diff_chinese( $begin_time, $end_time ){
    $diff_array = timediff( $begin_time, $end_time );
    return number2Chinese($diff_array['day'],1);
}

function get_diff_day( $begin_time, $end_time){
    $diff_array = timediff( $begin_time, $end_time );
    return $diff_array['day'];
}

/**
 * 获取表的数据
 * @param $tablename
 * @param int $listRows
 * @return array
 */
function get_table($tablename,$listRows=8){
    $table = D($tablename);
    $page = I('p');
    $data =  $table->page($page,$listRows)->select();
    $count = $table->count();
    $page  = new \Think\PageCms($count,$listRows);
    $page = $page->show_yii();
    if(false === $data){
        return $this->_empty("获取数据失败！");
    }
    $returnArr = array('lists'=>$data,'page'=>$page);
    //var_dump($returnArr);
    return  $returnArr;

}

/**
 * 获得首页封面
 * @return string
 */
function get_home_url(){
    if(C('IS_WELCOME')){
        return U('Index/home');
    }else{
        return '/';
    }
}


function is_mobile() {

    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        return true;

    //此条摘自TPM智能切换模板引擎，适合TPM开发
    if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT'])
        return true;
    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
        //找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
    //判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
        );
        //从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    //协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

/**
 * 图集字段转换为 图片路径信息
 * @param string $ids     图片集ids , 例如: 1,2,3
 * @param string $field   获取单个字段还是 整个对象
 * @param bool $return_no_pic   是否返回默认值
 * @return array
 * 可以反馈指定字段  : 图片路径字段为 path
 * 可以返回整个对象
 */
function get_tuji($ids='',$field = '',$return_no_pic = true){
    $imgids = str2arr($ids);
    foreach($imgids as $key){
        $list[] = get_cover($key,$field,$return_no_pic);
    }
    return $list;
}


/**
 * 根据类型获取配置的值  $type = host  根据域名获取 $type 默认是数组的key
 * @param $type
 * @param $config_name
 * @return mixed
 */
function get_config_val($config_name,$type=''){

    if($type =='host'){
        $key = strtolower($_SERVER['HTTP_HOST']) ;
    }else{
        $key = $type ;
    }

    $data = C($config_name);

    return is_array($data) ? $data[$key] : $data;
}

/**
 * 获取日期的中文星期几
 * @param string $day
 * @param string $header
 * @return string
 */
function get_week($day='',$header='星期'){

    $weekarray=array("日","一","二","三","四","五","六");

    return $header.$weekarray[date("w",$day)];

}
/**
 * 给字符串前面追加新的字符
 * @param string $strs
 * @param string $newstr
 * @return string
 */
function arrayAppendStr($strs,$newstr){
    if(empty($strs)){
		return $strs;
	}
	$array= '';
	$array = explode(',', $strs);  
	foreach($array as $key => $val){
		
	$array[$key] = $newstr.$val;	
		
	}
	$strnew = implode(',',$array); 

   return $strnew;
}