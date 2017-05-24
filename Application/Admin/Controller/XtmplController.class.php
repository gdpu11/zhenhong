<?php

namespace Admin\Controller;

class XtmplController extends AdminController {

    public function index(){

    }

    public function editblock($id){

        $code = $this->get_tmpl($id);

        echo $code;

        $this->display();
    }

    private function get_tmpl($blockId=0){

        //获取模块数据
        $xblock = M('xblock');
        $block = $xblock->find($blockId);
        $attribute = $block['attribute'];

        if(!empty($block)){
            //获取模板数据
            $tmplId = $block['tmplid'];
            $xtmpl = M('xtmpl');
            $tmpl = $xtmpl->find($tmplId);

            $code = $tmpl['code'];

        }
        return $code;

    }
}