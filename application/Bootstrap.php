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


        // 加载核心组件
        Yaf_Loader::import(CORE_PATH.'/C_Basic.php');
        Yaf_Loader::import(CORE_PATH.'/Helper.php');
        Yaf_Loader::import(CORE_PATH.'/Model.php');

        // 导入 F_Basic.php 与 F_Network.php
        Helper::import('Basic');
        Helper::import('Network');
    }

    public function __initLoad(){
        Yaf_Loader::import(APP_PATH.'/application/init.php');
    }





}


