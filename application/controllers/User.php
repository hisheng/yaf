<?php
/**
 * Created by PhpStorm.
 * User: zhanghaisheng
 * Date: 2016/9/1
 * Time: 10:20
 */

class UserController extends BasicController{

    public function init(){
        //初始化的时候，默认不开启 view解析
        Yaf_Dispatcher::getInstance()->autoRender(false);
    }

    public function showAction(){
        $User=$this->load('user');
        var_dump($User->show());
    }

}