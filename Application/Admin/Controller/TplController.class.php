<?php
// +----------------------------------------------------------------------
// | 成都龙兵科技 http://www.xbjianzhan.com/
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台元素组件
 * @author By.jingshuixian
 */
class TplController extends AdminController {


    public function num($name="",$value=""){
        //<input type="text" class="text input-mid" name="{$field.name}" value="{$data[$field['name']]}">

        $this->assign('name', $name);
        $this->assign('value', $value);
        $this->display('Tpl/num');
    }



    public function image($config = array()){

        $this->assign('name', $config['name']);
        $this->assign('value', $config['value']);
        $this->assign('default', $config['default']);
        $this->assign('options', $config['options']);
        $this->display('Tpl/image');

    }

    /**
     * 上传单个图片
     */
    public function upload_image($name,$value,$options){
        $this->assign('name', $name);
        $this->assign('value', $value);
        $this->assign('options', $options);
        $this->display('Tpl/upload_image');
    }

    public function textarea($name,$value,$options){
        $this->assign('name', $name);
        $this->assign('value', $value);
        $this->assign('options', $options);
        $this->display('Tpl/textarea');
    }

    public function editor($name,$value,$options){
        $this->assign('name', $name);
        $this->assign('value', $value);
        $this->assign('options', $options);
        $this->display('Tpl/editor');
    }

    public function all($tmpl, $name,$value,$options){
        $this->assign('name', $name);
        $this->assign('value', $value);
        $this->assign('options', $options);
        $this->display('Tpl/'.$tmpl);
    }
}
