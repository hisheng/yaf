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
        echo 'index/test';
    }

    public function redis2Action(){



//        $isHide=$this->Redis_->hGet('gift_setad', 'isHide');
//        if ($isHide == 'true') {
//            $isHide1 = true;
//        }else{
//            $isHide1 = false;
//        }
//
//
//        $url=$this->Redis_->hGet('gift_setad', 'url');
//        if (empty($url)) {
//            $url1='';
//        }else{
//            $url1=$url;
//        }
//
//
//        $b=array();
//        $b['isHide']=$isHide1;
//        $b['url']=trim($url1);
//        $b['giftId']='0';
//        $b['count']=0;
//        $b['imgurl']=$this->Redis_->hGet('gift_setad', 'imgurl');
//        print_r(json_encode($b));
//


      /*  $a = new \HttpFoundation\Request();
        $a->get();*/


    }

}
