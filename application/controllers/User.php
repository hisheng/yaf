<?php
/**
 * Created by PhpStorm.
 * User: zhanghaisheng
 * Date: 2016/9/1
 * Time: 10:20
 */

class UserController extends BasicController{


    public function showAction(){
        $User=$this->load('user');
        var_dump($User->show());
    }

}