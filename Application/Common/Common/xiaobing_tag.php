<?php


/**
 * 获取content内容
 * @param $obj
 * @return string
 *
 */
function get_content($obj){
    if(empty($obj['content'])){
        return  '没有添加内容,请到后台添加!';
    }else{
        return $obj['content'];
    }
}

/**
 * 获得扩展信息
 * @param $category
 * @param $pCategory
 * @param $topCategory
 * @param int $num
 * @return mixed
 */
function get_kuozhan($category,$pCategory,$topCategory,$num = 1){
    $field = 'kuozhan'.$num ;
    return get_cate_filed($category,$pCategory,$topCategory,$field) ;

}

/**
 * 获取分类的字段信息
 * @param $category
 * @param $pCategory
 * @param $topCategory
 * @param $field
 * @return mixed
 */
function  get_cate_filed($category,$pCategory,$topCategory,$field){
    $str = '' ;

    if(!empty($category[$field]) && C('NO_PIC') != $category[$field]){ //本身
        $str = $category[$field] ;
    }elseif(!empty($pCategory[$field])   && C('NO_PIC') != $pCategory[$field] ){ //父级
        $str =  $pCategory[$field] ;
    }elseif(!empty($topCategory[$field])   &&  C('NO_PIC') != $topCategory[$field] ){ //顶级
        $str = $topCategory[$field] ;
    }

    if(empty($str)){
        $str_config = C('ZX_DEFAULT');
        $str = $str_config[$field];
        if( $str > 0 &&  in_array($field , array('banner','cover','icon'))){
            $str = get_cover($str);
        }
    }

    return $str;

}

/**
 * 获取换行信息
 * @param $key
 * @param int $num
 * @param string $class
 * @return string
 */
function get_br($key ,$num = 3,$class = 'br'){
    return $key%$num == ($num-1) ? $class : '' ;
}

