<?php
/**
 * Created by PhpStorm.
 * User: zhanghaisheng
 * Date: 2016/9/20
 * Time: 17:28
 */
use \Michelf\Markdown;

class WikiController extends BasicController {
    protected $Redis_;
    public function init(){
        //关闭 view渲染
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $this->Redis_= new Redis;
        $this->Redis_->connect('127.0.0.1',6379);
    }

    public function indexAction(){
        Yaf_Dispatcher::getInstance()->autoRender(true);

        $text = file_get_contents('rd.md');
        $html = Markdown::defaultTransform($text);

        $this->getView()->assign("html", $html);


    }


}