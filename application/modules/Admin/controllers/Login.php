<?php
/**
 * Created by PhpStorm.
 * User: zhanghaisheng
 * Date: 2016/9/1
 * Time: 11:04
 */

class LoginController extends BasicController{
    public function inAction(){
        //获取视图引擎
        $view = $this->getView();
        var_dump($view);

        //设置传递的变量和参数
        $view->assign('key','value');
        $p = array('k1'=>'v1','k2'=>'v2');
        $view->assign('key2',$p);


    }
}