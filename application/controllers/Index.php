<?php

class IndexController extends Yaf_Controller_Abstract {
    protected $Redis_;

	public function init(){
        //关闭 view渲染
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $this->Redis_= new Redis;
        $this->Redis_->connect('127.0.0.1',6379);
	}

	public function indexAction() {
        echo 'index/index';

        //test
        echo \Monolog\Logger::DEBUG;

    }

    public function testAction(){
        //获取 请求的实例
        $http = $this->getRequest();
        var_dump($http);

        //获取控制器 实例

        echo 'index/test';
        var_dump($http->getServer());
        var_dump($http->get('name'));
        var_dump($http->getCookie());
        var_dump($http->getQuery());
        var_dump($http->getPost());
        var_dump($http->getParams());



    }

    public function redis2Action(){
    }

}
