<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2013 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi.cn@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace OT\TagLib;
use Think\Template\TagLib;
/**
 * OT系统标签库
 */
class Cms extends TagLib{
    // 标签定义
    protected $tags   =  array(
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'nav'       =>  array('attr' => 'field,name', 'close' => 3), //获取导航
        'query'     =>  array('attr'=>'sql,result','close'=>0),
        'cate'      =>  array('attr'=>'id,name,limit,pid,result,self','level'=>3),
        'article'   =>  array('attr'=>'id,name,cate,pid,pos,type,limit,where,order,field,result','level'=>3),
        'arc'   =>  array('attr'=>'id,name,cate,pid,pos,type,limit,where,order,field,result','level'=>3), //article 名称的标签精简版
        'value'     =>  array('attr'=>'name,table,where,type,field,filter','alias'=>'max,min,avg,sum,count','close'=>0),
        'data'      =>  array('attr'=>'name,field,limit,order,where,join,group,having,table,result,gc','level'=>2),
        'datalist'  =>  array('attr'=>'name,field,limit,order,where,table,join,having,group,result,count,key,mod,gc','level'=>3),
        'prev'     => array('attr' => 'name,info', 'close' => 1), //获取上一篇文章信息
        'next'     => array('attr' => 'name,info', 'close' => 1), //获取下一篇文章信息
        'page'     => array('attr' => 'cate,listrow,result', 'close' => 0), //列表分页
        'link'       =>  array('attr' => 'result', 'close' => 1,'level'=>1), //获取导航
        'pos'       =>  array('attr' => 'result', 'close' => 1,'level'=>1), //获取当前位置
        'banner'       =>  array('attr' => 'result', 'close' => 1,'level'=>1), //获取banner
        'fnav'       =>  array('attr' => 'field,name', 'close' => 1), //获取导航
        );

    /* 导航列表 */
    public function _nav($tag, $content){

        $result = empty($tag['result']) ? 'data' : $tag['result'];
        $field  = empty($tag['field']) ? 'true' : $tag['field'];
        $tree   =   empty($tag['tree'])? false : true;
        $parse  = $parse   = '<?php ';
        $parse .= '$__NAV__ = M(\'Channel\')->field('.$field.')->where("status=1")->order("sort")->select();';
        if($tree){
            $parse .= '$__NAV__ = list_to_tree($__NAV__, "id", "pid", "_");';
        }
        $parse .= '?><volist name="__NAV__" id="'. $result .'">';
        $parse .= $content;
        $parse .= '</volist>';
        return $parse;
    }

    // sql查询
    public function _query($tag,$content) {
        $sql       =    $tag['sql'];
        $result    =    !empty($tag['result'])?$tag['result']:'result';
        $parseStr  =    '<?php $'.$result.' = M()->query("'.$sql.'");';
        $parseStr .=    'if($'.$result.'):?>'.$content;
        $parseStr .=    "<?php endif;?>";
        return $parseStr;
    }

    // 获取字段值 包括统计数据
    // type 包括 getField count max min avg sum
    public function _value($tag,$content,$type='getField'){
        $name   =   !empty($tag['name'])?$tag['name']:'Document';
        $type   =   !empty($tag['type'])?$tag['type']:$type;
        $filter =   !empty($tag['filter'])?$tag['filter']:'';
        $parseStr   =  '<?php echo '.$filter.'(M("'.$name.'")';
        if(!empty($tag['table'])) {
            $parseStr .= '->table("'.$tag['table'].'")';
        }
        if(!empty($tag['where'])){
            $tag['where']=$this->parseCondition($tag['where']);
            $parseStr .= '->where("'.$tag['where'].'")';
        }
        $parseStr .= '->'.$type.'("'.$tag['field'].'"));?>';
        return $parseStr;
    }

    public function _count($attr,$content){
        return $this->_value($attr,$content,'count');
    }
    public function _sum($attr,$content){
        return $this->_value($attr,$content,'sum');
    }
    public function _max($attr,$content){
        return $this->_value($attr,$content,'max');
    }
    public function _min($attr,$content){
        return $this->_value($attr,$content,'min');
    }
    public function _avg($attr,$content){
        return $this->_value($attr,$content,'avg');
    }

    public function _data($tag,$content){
        $name       =   !empty($tag['name'])?$tag['name']:'Document';
        $result     =   !empty($tag['result'])?$tag['result']:'article';
        $parseStr   =   '<?php $'.$result.' =M("'.$name.'")->alias("__DOCUMENT")';
        if(!empty($tag['table'])) {
            $parseStr .= '->table("'.$tag['table'].'")';
        }
        if(!empty($tag['where'])){
            $tag['where']=$this->parseCondition($tag['where']);
            $parseStr .= '->where("'.$tag['where'].'")';
        }
        if(!empty($tag['order'])){
            $parseStr .= '->order("'.$tag['order'].'")';
        }
        if(!empty($tag['join'])){
            $parseStr .= '->join("'.$tag['join'].'")';
        }
        if(!empty($tag['group'])){
            $parseStr .= '->group("'.$tag['group'].'")';
        }
        if(!empty($tag['having'])){
            $parseStr .= '->having("'.$tag['having'].'")';
        }
        if(!empty($tag['field'])){
            $parseStr .= '->field("'.$tag['field'].'")';
        }
        $parseStr .= '->find();';
        $parseStr .= '$' . $result . ' = format_item_data($' . $result . ',"arc");';
        $parseStr .=  ' ?>'.$content;
        if(!empty($tag['gc'])) {
            $parseStr .= '<?php unset($'.$result.');?>';
        }
        return $parseStr;
    }

    public function _datalist($tag,$content) {
        $name       =   !empty($tag['name'])?$tag['name']:'Document';
        $result     =   !empty($tag['result'])?$tag['result']:'data';
        $key        =   !empty($tag['key'])?$tag['key']:'i';
        $mod        =   isset($tag['mod'])?$tag['mod']:'2';
        $count      =   isset($tag['count'])?$tag['count']:'count';
        $parseStr   =   '<?php $_result = M("'.$name.'")->alias("__DOCUMENT")';
        if(!empty($tag['table'])) {
            $parseStr .= '->table("'.$tag['table'].'")';
        }
        if(!empty($tag['where'])){
            $tag['where']=$this->parseCondition($tag['where']);
            $tag['where']= str_replace('{','".',$tag['where']);
            $tag['where']= str_replace('}','."',$tag['where']);
            $parseStr .= '->where("'.$tag['where'].'")';
        }
        if(!empty($tag['order'])){
            $parseStr .= '->order("'.$tag['order'].'")';
        }
        if(!empty($tag['join'])){
            $parseStr .= '->join("'.$tag['join'].'")';
        }
        if(!empty($tag['group'])){
            $parseStr .= '->group("'.$tag['group'].'")';
        }
        if(!empty($tag['having'])){
            $parseStr .= '->having("'.$tag['having'].'")';
        }
        if(!empty($tag['limit'])){
            $parseStr .= '->limit("'.$tag['limit'].'")';
        }
        if(!empty($tag['field'])){
            $parseStr .= '->field("'.$tag['field'].'")';
        }
        $parseStr .= '->select();';
        $parseStr .= '$_result = forma_list_data($_result,"arc") ;  ';
        $parseStr .='if($_result):$'.$key.'=0;foreach($_result as $key=>$'.$result.'): ';
        $parseStr .='$'.$count.' =count($_result);';

        $parseStr .= '++$'.$key.';$mod = ($'.$key.' % '.$mod.' );?>'.$content;
        if(!empty($tag['gc'])) {
            $parseStr .= '<?php unset($'.$result.');?>';
        }
        $parseStr .= '<?php endforeach; endif;?>';
        return $parseStr;
    }

    // 获取分类信息
    public function _cate($tag,$content){
        $result      =  !empty($tag['result'])?$tag['result']:'data';
        if(!empty($tag['id'])) {
            if(strpos($tag['id'],',')) { //带多个id的时间直接查询id集合
                $parseStr   =  '<?php $__cate__ = M("Category")->where( "id IN('.$tag['id'].')")->select();';
                $parseStr .= '$__cate__ = forma_list_data($__cate__,"cate");';
                $parseStr  .=  'foreach($__cate__ as $key=>$'.$result.'): ';
                $parseStr .=  'if($__cate__):?>'.$content.'<?php endif; endforeach;?>';


            }else{
                // 获取单个分类
                $parseStr   =  '<?php $'.$result.' = M("Category")->find('.$tag['id'].');';
                $parseStr .= '$' . $result . ' = format_item_data($' . $result . ',"cate");';
                $parseStr .=  'if($'.$result.'):?>'.$content;
                $parseStr .= "<?php endif;?>";

            }


        }elseif(!empty($tag['name'])) {
            // 获取单个分类
            $parseStr   =  '<?php $'.$result.' = M("Category")->getByName('.$tag['name'].');';
            $parseStr .= '$' . $result . ' = format_item_data($' . $result . ',"cate");';
            $parseStr .=  'if($'.$result.'):?>'.$content;
            $parseStr .= "<?php endif;?>";
        }else{  //elseif(!empty($tag['pid']))  改为 else  By.jingshuixian
            $key     =   !empty($tag['key'])?$tag['key']:'i';
            $mod    =   isset($tag['mod'])?$tag['mod']:'2';
            $pid = !empty($tag['pid'])? $tag['pid']:'0';
            //是否包含自己
            $self   =   empty($tag['self']) ? 'false' : 'true';
            $tag['rootid'] = empty($tag['rootid'])? 0:$tag['rootid'];
            //echo ' 11'.$tag['rootid'];
            trace( $tag,'$tag');
            /*$where = '';
            if($pid > 0){
                $ids = get_child_category_ids($pid);
                trace( $ids,'$ids');
                $where = ' AND id IN ('.$ids.') ';
                trace( $where,'$where');
            }*/

            $parseStr   =  '<?php ';
            $parseStr .= '$_where = ""; ';
            //By.jingshuixian  写到这里 是为了传递动态id   如果pid 大于0  就获得他的子类
            $parseStr .= 'if(' . $tag['pid']. ' > 0):$_ids = get_child_category_ids(' . $tag['pid']. ','.$self.',3); ';

            $parseStr .= ' if(empty($_ids)) : $_ids = 0;  ';  // if   子类是否为空 开始
            $parseStr .= 'endif;'; // if   子类是否为空  结束

            $parseStr .= ' $_where = " AND id IN (".$_ids.")" ; endif; ';

            $parseStr .= '$_result = M("Category")->order("pid , sort")->where("display=1 AND hidden=1 AND status=1" .$_where)';
            if(!empty($tag['limit'])){
                $parseStr .= '->limit('.$tag['limit'].')';
            }
            $parseStr .= '->select(); ';
            $parseStr .= '$_result = forma_list_data($_result) ;  ';
            $parseStr .='$rootid = 0;   if(' . $tag['pid']. ' > 0 && !'.$self.'): $rootid =' . $tag['pid']. ' ;  endif;';

            //$parseStr .= ' $_result = list_to_tree($_result,"id","pid","_child",'.$tag['rootid'].') ;  ';
            $parseStr .= ' $_result = list_to_tree($_result,"id","pid","_child",$rootid) ;  ';
           // $parseStr .= 'echo $rootid ';
            //$parseStr .= 'var_dump($_result);';
            $parseStr  .=  'if($_result):$'.$key.'=0;foreach($_result as $key=>$'.$result.'): ';
            $parseStr .= '++$'.$key.';$mod = ($'.$key.' % '.$mod.' );';
            $parseStr .=  'if($'.$result.'):?>'.$content.'<?php endif; endforeach;?>';
            $parseStr .= "<?php endif;?>";
        }

        return $parseStr;
    }
    public function _arc($tag,$content){
            return $this->_article($tag,$content);
    }
    public function _article($tag,$content){
        $result      =  !empty($tag['result'])?$tag['result']:'data';
        $name	=	!empty($tag['name'])?$tag['name']:'Article';
        $order   =  empty($tag['order'])?'level desc,create_time desc':$tag['order'];
        $field  =   empty($tag['field'])?'*':$tag['field'];
        $son = $tag['son']=='true' ? true : false;

        $join   =   'INNER JOIN __DOCUMENT_'.strtoupper($name).'__ ON __DOCUMENT.id = __DOCUMENT_'.strtoupper($name).'__.id';
        if(!empty($tag['id'])) { // 获取单个数据
            if(strpos($tag['id'],'[')){ // 有 [] 的时间默认为 传递的数据 比如 $cate['9']
                //$where .= ' AND category_id IN (".' . $tag['id'] . '.")';
                $arc =  $this->_data(array('name'=>"Document", 'where'=>'status=1 AND __DOCUMENT.id=".'.$tag['id'].'."', 'field'=>$field,'result'=>$result,'order'=>$order,'join'=>$join),$content);

            }else{
                $arc =  $this->_data(array('name'=>"Document", 'where'=>'status=1 AND __DOCUMENT.id='.$tag['id'], 'field'=>$field,'result'=>$result,'order'=>$order,'join'=>$join),$content);
            }
                format_item_data($arc,'arc');
            return $arc ;
        }else{ // 获取数据集
            $where = 'status=1 ';

            if(!empty($tag['model'])) {
                $where .= ' AND model_id='.$tag['model'];
            }
            if(!empty($tag['cate'])) { // 获取某个分类的文章
                if(strpos($tag['cate'],',')) { //带多个id的时间直接查询id集合

                    $where .= ' AND category_id IN (' . $tag['cate'] . ')';
                    //echo $where;
                }elseif(strpos($tag['cate'],'[')){ // 有 [] 的时间默认为 传递的数据 比如 $cate['9']


                    if($son){ //包含子分类
                        //$where .= ' AND category_id IN ('.get_child_category_ids($tag['cate'],true).')';
                       // $where .= ' AND category_id IN (".' . $tag['cate'],true) . '.")';
                        $where .= ' AND category_id IN (".get_child_category_ids(' . $tag['cate'] . ',true).")';
                    }else { // 只显示一个分类的内容
                        $where .= ' AND category_id IN (".' . $tag['cate'] . '.")';
                    }

                    //parseStr .= '->where("'.$tag['where'].'")';
                }else{ //单个 分类id的情况  
                    if($son){ //包含子分类
                        $where .= ' AND category_id IN ('.get_child_category_ids($tag['cate'],true).')';
                    }else { // 只显示一个分类的内容
                        $where .= ' AND category_id=' . $tag["cate"];
                    }
                }
            }

            if(!empty($tag['pid'])){ //
                $where .= ' AND pid = '.$tag['pid'];
            }
            if(!empty($tag['pos'])) {
                $where .= ' AND position ='.$tag['pos'];
            }
            if(!empty($tag['where'])) {
                $where  .=  ' AND '.$tag['where'];
            }
            return $this->_datalist(array('name'=>'Document','where'=>$where,'field'=>$field,'result'=>$result,'order'=>$order,'join'=>$join,'limit'=>!empty($tag['limit'])?$tag['limit']:''),$content);
        }
    }

    /* 获取下一篇文章信息 */
    public function _next($tag, $content){

        $result = empty($tag['result']) ? 'data' : $tag['result'];
        $info   = empty($tag['info'])?'$info':$tag['info'];

        $parse  = '<?php ';
        $parse .= '$' . $result . ' = D(\'Document\')->next(' . $info . ');';
        $parse .= '$' . $result . ' = format_item_data($' . $result . ',"arc");';
        $parse .= ' ?>';
        $parse .= '<notempty name="' . $result . '">';
        $parse .= $content;
        $parse .= '</notempty>';
        return $parse;
    }

    /* 获取上一篇文章信息 */
    public function _prev($tag, $content){
        $result = empty($tag['result']) ? 'data' : $tag['result'];
        $info   = empty($tag['info'])?'$info':$tag['info'];

        $parse  = '<?php ';
        $parse .= '$' . $result . ' = D(\'Document\')->prev(' . $info . ');';

        $parse .= '$' . $result . ' = format_item_data($' . $result . ',"arc");';

        $parse .= ' ?>';
        $parse .= '<notempty name="' . $result . '">';
        $parse .= $content;
        $parse .= '</notempty>';
        return $parse;
    }
    /* 列表数据分页 */
    public function _page($tag,$content){

        $cate    = empty($tag['cate']) ? '$category["id"]' : $tag['cate'];
        $listrow = empty($tag['listrow']) ? '$category["listrow"]': $tag['listrow'];
        $theme = $tag['theme'];
        $prev = $tag['prev'];
        $next = $tag['next'];

        $result = empty($tag['result']) ? 'data' : $tag['result'];
        $parse   = '<?php ';
        //$parse .= 'print_r($filter);';
        $parse  .= '$__PAGE__ = new \Think\PageCms(get_list_count(' . $cate . '), ' . $listrow . ', "' . $theme . '");';
        if($tag['class'] == 'bootstrap'){
            $parse  .= 'echo $__PAGE__->show_bootstrap(); ';
        }elseif($tag['class'] == 'mobile'){
            $parse  .= '$__PAGE__->setConfig("theme_yii","%UP_PAGE%  <li class=paging_total><span>%NOW_PAGE%/%TOTAL_PAGE%</span></li> %DOWN_PAGE%");  echo $__PAGE__->show_yii(); ';
        }elseif($tag['class'] == 'en') {

            $parse  .= '$__PAGE__->setConfig("header","<li><a>Total %TOTAL_ROW% </a></li>");  ';
            $parse  .= '$__PAGE__->setConfig("prev","Prev");  ';
            $parse  .= '$__PAGE__->setConfig("next","Next");  ';
            $parse  .= '$__PAGE__->setConfig("first","First");  ';
            $parse  .= '$__PAGE__->setConfig("last","Last");  ';
            $parse  .= ' echo $__PAGE__->show_yii(); ';

        }else{
            if(!empty($prev)){
                $parse  .=  '$__PAGE__->setConfig("prev",' . $prev . '); ';
            }
            if(!empty($next)){
                $parse  .=  '$__PAGE__->setConfig("next",' . $next . '); ';
            }
            $parse  .= 'echo $__PAGE__->show_yii(); ';
        }


        $parse  .= ' ?>';
        return $parse;
    }


    /* 导航列表 */
    public function _link($tag, $content){

        $result = empty($tag['result']) ? 'data' : $tag['result'];
        $limit = empty($tag['limit']) ? 1000 : $tag['limit'];
        $tag['where']=$this->parseCondition($tag['where']);
        $tag['where']= str_replace('{','".',$tag['where']);
        $tag['where']= str_replace('}','."',$tag['where']);
        $where = empty($tag['where']) ? ' hide=0 ' : ( ' hide=0 AND ' . $tag['where']);




        $parse  = $parse   = '<?php ';
        $parse .= '$__LINK__ = M(\'Links\')->where("'. $where .'")->order("sort desc , id desc")->limit('.$limit.')->select();';

        $parse .= '$__LINK__ = forma_list_data($__LINK__, "", array("img"=>"logo"));';

        $parse .= '?><volist name="__LINK__" id="'. $result .'">';
        $parse .= $content;
        $parse .= '</volist>';
        return $parse;
    }

    public function _pos($tag, $content){

        $result = empty($tag['result']) ? 'data' : $tag['result'];

        $parse  = $parse   = '<?php ';
        $parse .= ' if(!empty(' . $tag['id']. ')) :  $__NAV__ = array();';  // if   子类是否为空 开始

        $parse .= '$__NAV__ = got_pos(' . $tag['id']. ',$__NAV__);';
        $parse .= ' $__NAV__ = forma_list_data($__NAV__);';

        $parse .= 'endif;'; // if   子类是否为空  结束

        $parse .= '?><volist name="__NAV__" id="'. $result .'">';
        $parse .= $content;
        $parse .= '</volist>';
        return $parse;
    }

    /* 导航列表 */
    public function _banner($tag, $content){

        $result = empty($tag['result']) ? 'data' : $tag['result'];

        $parse  = $parse   = '<?php ';
        $parse .= '$__LINK__ = M(\'Banner\')->where("'.$tag['where'].'")->order("sort desc , id desc")->select();';

        $parse .= '$__LINK__ = forma_list_data($__LINK__, "", array("img"=>"img"));';

        $parse .= '?><volist name="__LINK__" id="'. $result .'">';
        $parse .= $content;
        $parse .= '</volist>';
        return $parse;
    }
    /* 导航列表 */
    public function _fnav($tag, $content){

        $field  = empty($tag['field']) ? 'true' : $tag['field'];
        $tree   =   empty($tag['tree'])? false : true;
        $parse  = $parse   = '<?php ';
        $parse .= '$__NAV__ = M(\'Channel\')->field('.$field.')->where("status=1")->order("sort")->select();';
        if($tree){
            $parse .= '$__NAV__ = list_to_tree($__NAV__, "id", "pid", "_child");';
        }
        $parse .= '?><volist name="__NAV__" id="'. $tag['name'] .'">';
        $parse .= $content;
        $parse .= '</volist>';
        return $parse;
    }
}