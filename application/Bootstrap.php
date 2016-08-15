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

    public function __initLoad(){
        Yaf_Loader::import(APP_PATH.'/application/init.php');
        Yaf_Loader::import(APP_PATH.'/application/library/vendor/autoload.php');
    }





}


