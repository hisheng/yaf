<?php


class MyController extends BasicController
{
    /*protected  function init(){
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
    }*/
    protected $Zuopin_;
    protected $Redis_;

    protected function init()
    {
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $this->Zuopin_ = $this->load('Zuopin');
        $this->Redis_ = new Redis;
        $this->Redis_->connect('127.0.0.1', 6379);

    }

}