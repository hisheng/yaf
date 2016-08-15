<?php

class IndexController extends Yaf_Controller_Abstract {

	public function init(){
        //关闭 view渲染
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
	}

	public function indexAction() {

        echo 'index/index';


    }

}
