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

    //参数获取  .../admin/login/p/hello/3/hi/33
    public function pAction($hello,$hi){

        echo $this->getParam('hello');
        echo '<br/>';
        echo $this->getParam('hi');

        //view 不用解析
        Yaf_Dispatcher::getInstance()->autoRender(false);
    }
}