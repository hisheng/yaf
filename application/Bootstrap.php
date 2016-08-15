<?php

class Bootstrap extends Yaf_Bootstrap_Abstract{

    public function _initEcho(){
       /* echo "我是努力学习的小鸟";
        $b=Yaf_Loader::getInstance();
        print_r($b); 
        //测试Yaf_Registry
        $h= new HelloWorld(); 
        Yaf_Registry::set('helloWorld',$h);*/
    }

    // Load libaray, MySQL model, function
    public function _initCore() {
        // 设置自动加载的目录
        ini_set('yaf.library', LIB_PATH);
        
        // 加载核心组件
        Yaf_Loader::import(CORE_PATH.'/C_Basic.php');
        Yaf_Loader::import(CORE_PATH.'/Helper.php');
        Yaf_Loader::import(CORE_PATH.'/Model.php');
        Yaf_Loader::import(LIB_PATH.'/yar/Yar_Basic.php');

        // 导入 F_Basic.php 与 F_Network.php
        Helper::import('Basic');
        Helper::import('Network');
    }

    // 这里我们添加三种路由，分别为 rewrite, rewrite_category, regex
    // 用于 url rewrite 的讲解


    public function _initRedis() {
        if(extension_loaded('Redis')){
            $config = Yaf_Application::app()->getConfig()->toArray();       
             
            $queue = 'test_queue';
            $host  = $config['redis_host'];
            $port  = $config['redis_port']; 
            $redis = new Redis();
            $redis->connect($host, $port);

            Yaf_Registry::set('redis', $redis);  
            
        }
    }

    public function _initPlugin(Yaf_Dispatcher $dispatcher) {
        $router = new RouterPlugin();
        $dispatcher->registerPlugin($router);

        $admin = new AdminPlugin();
        $dispatcher->registerPlugin($admin);
        
        Yaf_Registry::set('adminPlugin', $admin);
    }

}


