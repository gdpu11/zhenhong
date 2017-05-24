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
namespace Think;

class PageCms{
    public $firstRow; // 起始行数
    public $listRows; // 列表每页显示行数
    public $parameter; // 分页跳转时要带的参数
    public $totalRows; // 总行数
    public $totalPages; // 分页总页面数
    public $rollPage   = 11;// 分页栏每页显示的页数
	public $lastSuffix = false; // 最后一页是否显示总页数
	public $theme = 'theme_yii';

    private $p       = 'p'; //分页参数名
    private $url     = ''; //当前链接URL
    private $nowPage = 1;
	
	

	// 分页显示定制
    private $config  = array(
        'header' => '<li class="total" ><a>共 %TOTAL_ROW% 条</a></li>',
        'prev'   => '上一页',
        'next'   => '下一页',
        'first'  => '首页',
        'last'   => '尾页',
        'theme'  => '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%',
        //'theme_yii'  => '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%',
        'theme_yii'  => '%UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %HEADER% ',
        'theme_hongzhuan'  => '%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%',
    );

    /**
     * 架构函数
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     */
    public function __construct($totalRows, $listRows=8, $theme = "theme_yii" , $parameter = array()) {
        C('VAR_PAGE') && $this->p = C('VAR_PAGE'); //设置分页参数名称
        /* 基础设置 */
        $this->totalRows  = $totalRows; //设置总记录数
        $this->listRows   = $listRows;  //设置每页显示行数
        //$this->rollPage   =$listRows;// 分页栏每页显示的页数
        $this->parameter  = empty($parameter) ? $_GET : $parameter;
        $this->nowPage    = empty($_GET[$this->p]) ? 1 : intval($_GET[$this->p]);
        $this->nowPage    = $this->nowPage>0 ? $this->nowPage : 1;
        $this->firstRow   = $this->listRows * ($this->nowPage - 1);
		$this->theme = empty($theme)? 'theme_yii': $theme;
    }

    /**
     * 定制分页链接设置
     * @param string $name  设置名称
     * @param string $value 设置值
     */
    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    /**
     * 生成链接URL
     * @param  integer $page 页码
     * @return string
     */
    private function url($page){
        return str_replace(urlencode('[PAGE]'), $page, $this->url);
    }

    /**
     * 组装分页链接
     * @return string
     */
    public function show() {
        if(0 == $this->totalRows) return '';

        /* 生成URL */
        $this->parameter[$this->p] = '[PAGE]';
        $this->url = U(ACTION_NAME, $this->parameter);
        /* 计算分页信息 */
        $this->totalPages = ceil($this->totalRows / $this->listRows); //总页数
        if(!empty($this->totalPages) && $this->nowPage > $this->totalPages) {
            $this->nowPage = $this->totalPages;
        }

        /* 计算分页零时变量 */
        $now_cool_page      = $this->rollPage/2;
		$now_cool_page_ceil = ceil($now_cool_page);
		$this->lastSuffix && $this->config['last'] = $this->totalPages;

        //上一页
        $up_row  = $this->nowPage - 1;
        $up_page = $up_row > 0 ? '<a class="prev" href="' . $this->url($up_row) . '">' . $this->config['prev'] . '</a>' : '';

        //下一页
        $down_row  = $this->nowPage + 1;
        $down_page = ($down_row <= $this->totalPages) ? '<a class="next" href="' . $this->url($down_row) . '">' . $this->config['next'] . '</a>' : '';

        //第一页
        $the_first = '';
        if($this->totalPages > $this->rollPage && ($this->nowPage - $now_cool_page) >= 1){
            $the_first = '<a class="first" href="' . $this->url(1) . '">' . $this->config['first'] . '</a>';
        }

        //最后一页
        $the_end = '';
        if($this->totalPages > $this->rollPage && ($this->nowPage + $now_cool_page) < $this->totalPages){
            $the_end = '<a class="end" href="' . $this->url($this->totalPages) . '">' . $this->config['last'] . '</a>';
        }

        //数字连接
        $link_page = "";
        for($i = 1; $i <= $this->rollPage; $i++){
			if(($this->nowPage - $now_cool_page) <= 0 ){
				$page = $i;
			}elseif(($this->nowPage + $now_cool_page - 1) >= $this->totalPages){
				$page = $this->totalPages - $this->rollPage + $i;
			}else{
				$page = $this->nowPage - $now_cool_page_ceil + $i;
			}
            if($page > 0 && $page != $this->nowPage){

                if($page <= $this->totalPages){
                    $link_page .= '<a class="num" href="' . $this->url($page) . '">' . $page . '</a>';
                }else{
                    break;
                }
            }else{
                if($page > 0 && $this->totalPages != 1){
                    $link_page .= '<span class="current">' . $page . '</span>';
                }
            }
        }

        //替换分页内容
        $page_str = str_replace(
            array('%HEADER%', '%NOW_PAGE%', '%UP_PAGE%', '%DOWN_PAGE%', '%FIRST%', '%LINK_PAGE%', '%END%', '%TOTAL_ROW%', '%TOTAL_PAGE%'),
            array($this->config['header'], $this->nowPage, $up_page, $down_page, $the_first, $link_page, $the_end, $this->totalRows, $this->totalPages),
            $this->config['theme']);
        return "<div>{$page_str}</div>";
    }

    public function show_yii() {
        if(0 == $this->totalRows) return '';

        /* 生成URL */
        //$this->parameter[$this->p] = '[PAGE]';
        //$this->url = U(ACTION_NAME, $this->parameter);

       /* if (empty($this->url)) {
            $this->parameter[$this->p] = '[PAGE]';
            $this->url = U(ACTION_NAME, $this->parameter);
        }else {
            $depr = C('URL_PATHINFO_DEPR');
            $this->url = rtrim(U('/'.$this->url,'',false),$depr).$depr.urlencode('[PAGE]').'.html';
        }*/

        $return = array();
        $obj = array();

        $this->parameter[$this->p] = '[PAGE]';

        /*
        if(ACTION_NAME =='search'){
            $this->url = U(ACTION_NAME.$depr.'keyword'.$depr.$this->parameter['keyword'].$depr.'p'.$depr.urlencode('[PAGE]'));
        }else{
            $this->url = U(ACTION_NAME.$depr.$this->parameter['category'].$depr.'p'.$depr.urlencode('[PAGE]'),$this->parameter);
        }*/
        $parameter = array();
        foreach($this->parameter as $key => $val){
            if(!in_array($key,array('category','m','p'))){
                $parameter[$key] = $val;
            }
        }

        if(!empty($this->parameter['category'])){
           $cate =  $this->parameter['category'] ;
        }

        $this->setUrl($cate ,$parameter);

        //print_r($this->parameter);
        //$this->url = U(ACTION_NAME, $this->parameter);

        /* 计算分页信息 */
        $this->totalPages = ceil($this->totalRows / $this->listRows); //总页数
        if(!empty($this->totalPages) && $this->nowPage > $this->totalPages) {
            $this->nowPage = $this->totalPages;
        }

        /* 计算分页零时变量 */
        $now_cool_page      = $this->rollPage/2;
        $now_cool_page_ceil = ceil($now_cool_page);
        $this->lastSuffix && $this->config['last'] = $this->totalPages;

        //上一页
        $up_row  = $this->nowPage - 1;
        $up_page = $up_row > 0 ? '<li class="paging_prev" ><a href="' . $this->url($up_row) . '">' . $this->config['prev'] . '</a></li>' : '';
        if($up_row > 0){
            $obj['href'] =  $this->url($up_row);
            $obj['title'] = $this->config['prev'];
            $obj['class'] = 'prev';
            $return[] = $obj;
        }


        //下一页
        $down_row  = $this->nowPage + 1;
        $down_page = ($down_row <= $this->totalPages) ? '<li class="paging_next" ><a href="' . $this->url($down_row) . '">' . $this->config['next'] . '</a></li>' : '';
        if($down_row <= $this->totalPages){
            $obj['href'] =  $this->url($down_row);
            $obj['title'] = $this->config['next'];
            $obj['class'] = 'next';
            $return[] = $obj;
        }


        //第一页
        $the_first = '';
        if($this->totalPages > $this->rollPage && ($this->nowPage - $now_cool_page) >= 1){
           //$the_first = '<a class="first" href="' . $this->url(1) . '">' . $this->config['first'] . '</a>';
            $the_first .= '<li class="paging_first" ><a href="' . $this->url(1) . '">' . $this->config['first'] . '</a></li>';

            $obj['href'] =  $this->url(1);
            $obj['title'] = $this->config['first'];
            $obj['class'] = 'first';
            $return[] = $obj;

        }

        //最后一页
        $the_end = '';
        if($this->totalPages > $this->rollPage && ($this->nowPage + $now_cool_page) < $this->totalPages){
            //$the_end = '<a class="end" href="' . $this->url($this->totalPages) . '">' . $this->config['last'] . '</a>';
            $the_end .= '<li class="paging_last" ><a href="' . $this->url($this->totalPages) . '">' . $this->config['last'] . '</a></li>';

            $obj['href'] =  $this->url($this->totalPages);
            $obj['title'] = $this->config['first'];
            $obj['class'] = 'last';
            $return[] = $obj;

        }

        //数字连接
        $link_page = "";
        for($i = 1; $i <= $this->rollPage; $i++){
            if(($this->nowPage - $now_cool_page) <= 0 ){
                $page = $i;
            }elseif(($this->nowPage + $now_cool_page - 1) >= $this->totalPages){
                $page = $this->totalPages - $this->rollPage + $i;
            }else{
                $page = $this->nowPage - $now_cool_page_ceil + $i;
            }
            if($page > 0 && $page != $this->nowPage){

                if($page <= $this->totalPages){
                    $link_page .= '<li class="paging_page" ><a href="' . $this->url($page) . '">' . $page . '</a></li>';
                    $obj['href'] =  $this->url($page);
                    $obj['title'] = $page;
                    $obj['class'] = '';
                    $return[] = $obj;
                }else{
                    break;
                }
            }else{
                if($page > 0 && $this->totalPages != 1){
                    $link_page .= '<li class="paging_current" ><a href="' . $this->url($page) . '">' . $page . '</a></li>';
                    $obj['href'] =  $this->url($page);
                    $obj['title'] = $page;
                    $obj['class'] = 'current';
                    $return[] = $obj;
                }
            }
        }

        //替换分页内容
        $page_str = str_replace(
            array('%HEADER%', '%NOW_PAGE%', '%UP_PAGE%', '%DOWN_PAGE%', '%FIRST%', '%LINK_PAGE%', '%END%', '%TOTAL_ROW%', '%TOTAL_PAGE%'),
            array($this->config['header'], $this->nowPage, $up_page, $down_page, $the_first, $link_page, $the_end, $this->totalRows, $this->totalPages),
            $this->config[$this->theme]);

        return  '<ul class="paging_ul" >'.$page_str.'</ul>';
    }


    public function show_bootstrap() {
        if(0 == $this->totalRows) return '';

        /* 生成URL */
        //$this->parameter[$this->p] = '[PAGE]';
        //$this->url = U(ACTION_NAME, $this->parameter);

        /* if (empty($this->url)) {
             $this->parameter[$this->p] = '[PAGE]';
             $this->url = U(ACTION_NAME, $this->parameter);
         }else {
             $depr = C('URL_PATHINFO_DEPR');
             $this->url = rtrim(U('/'.$this->url,'',false),$depr).$depr.urlencode('[PAGE]').'.html';
         }*/

        $return = array();
        $obj = array();

        $this->parameter[$this->p] = '[PAGE]';
        $depr = C('URL_PATHINFO_DEPR');
        if(ACTION_NAME =='search'){
            $this->url = U(ACTION_NAME.$depr.'keyword'.$depr.$this->parameter['keyword'].$depr.'p'.$depr.urlencode('[PAGE]'));
        }else{
            $this->url = U(ACTION_NAME.$depr.$this->parameter['category'].$depr.'p'.$depr.urlencode('[PAGE]'));
        }

        //print_r($this->parameter);
        //$this->url = U(ACTION_NAME, $this->parameter);

        /* 计算分页信息 */
        $this->totalPages = ceil($this->totalRows / $this->listRows); //总页数
        if(!empty($this->totalPages) && $this->nowPage > $this->totalPages) {
            $this->nowPage = $this->totalPages;
        }

        /* 计算分页零时变量 */
        $now_cool_page      = $this->rollPage/2;
        $now_cool_page_ceil = ceil($now_cool_page);
        $this->lastSuffix && $this->config['last'] = $this->totalPages;

        //上一页
        $up_row  = $this->nowPage - 1;
        $up_page = $up_row > 0 ? '<li class="paging_prev" ><a href="' . $this->url($up_row) . '">' . $this->config['prev'] . '</a></li>' : '';
        if($up_row > 0){
            $obj['href'] =  $this->url($up_row);
            $obj['title'] = $this->config['prev'];
            $obj['class'] = 'prev';
            $return[] = $obj;
        }


        //下一页
        $down_row  = $this->nowPage + 1;
        $down_page = ($down_row <= $this->totalPages) ? '<li class="paging_next" ><a href="' . $this->url($down_row) . '">' . $this->config['next'] . '</a></li>' : '';
        if($down_row <= $this->totalPages){
            $obj['href'] =  $this->url($down_row);
            $obj['title'] = $this->config['next'];
            $obj['class'] = 'next';
            $return[] = $obj;
        }


        //第一页
        $the_first = '';
        if($this->totalPages > $this->rollPage && ($this->nowPage - $now_cool_page) >= 1){
            //$the_first = '<a class="first" href="' . $this->url(1) . '">' . $this->config['first'] . '</a>';
            $the_first .= '<li class="paging_first" ><a href="' . $this->url(1) . '">' . $this->config['first'] . '</a></li>';

            $obj['href'] =  $this->url(1);
            $obj['title'] = $this->config['first'];
            $obj['class'] = 'first';
            $return[] = $obj;

        }

        //最后一页
        $the_end = '';
        if($this->totalPages > $this->rollPage && ($this->nowPage + $now_cool_page) < $this->totalPages){
            //$the_end = '<a class="end" href="' . $this->url($this->totalPages) . '">' . $this->config['last'] . '</a>';
            $the_end .= '<li class="paging_last" ><a href="' . $this->url($this->totalPages) . '">' . $this->config['last'] . '</a></li>';

            $obj['href'] =  $this->url($this->totalPages);
            $obj['title'] = $this->config['first'];
            $obj['class'] = 'last';
            $return[] = $obj;

        }

        //数字连接
        $link_page = "";
        for($i = 1; $i <= $this->rollPage; $i++){
            if(($this->nowPage - $now_cool_page) <= 0 ){
                $page = $i;
            }elseif(($this->nowPage + $now_cool_page - 1) >= $this->totalPages){
                $page = $this->totalPages - $this->rollPage + $i;
            }else{
                $page = $this->nowPage - $now_cool_page_ceil + $i;
            }
            if($page > 0 && $page != $this->nowPage){

                if($page <= $this->totalPages){
                    $link_page .= '<li class="paging_page" ><a href="' . $this->url($page) . '">' . $page . '</a></li>';
                    $obj['href'] =  $this->url($page);
                    $obj['title'] = $page;
                    $obj['class'] = '';
                    $return[] = $obj;
                }else{
                    break;
                }
            }else{
                if($page > 0 && $this->totalPages != 1){
                    $link_page .= '<li class="paging_current active" ><a href="' . $this->url($page) . '">' . $page . '</a></li>';
                    $obj['href'] =  $this->url($page);
                    $obj['title'] = $page;
                    $obj['class'] = 'active';
                    $return[] = $obj;
                }
            }
        }

        //替换分页内容
        $page_str = str_replace(
            array('%HEADER%', '%NOW_PAGE%', '%UP_PAGE%', '%DOWN_PAGE%', '%FIRST%', '%LINK_PAGE%', '%END%', '%TOTAL_ROW%', '%TOTAL_PAGE%'),
            array($this->config['header'], $this->nowPage, $up_page, $down_page, $the_first, $link_page, $the_end, $this->totalRows, $this->totalPages),
            $this->config[$this->theme]);

        return $page_str;
    }


    private function setUrl($cate,$parameter){
        $depr = C('URL_PATHINFO_DEPR');
        if(LONG_IIS6){

            $url[] = CONTROLLER_NAME;
            $url[] = ACTION_NAME;
            $url[] = 'category';
            $url[] = $cate;
            $url[] = 'p';
            $url[] = urlencode('[PAGE]');
            $this->url = U(arr2str($url,$depr) ,$parameter);
        }else{
            $this->url = U(ACTION_NAME.$depr.$cate.$depr.'p'.$depr.urlencode('[PAGE]'),$parameter);
        }

    }
}
