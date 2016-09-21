<?php
/**
 * Created by PhpStorm.
 * User: zhanghaisheng
 * Date: 2016/9/21
 * Time: 15:17
 */

class ApiController extends BasicController{
    protected $Redis_;
    public function init(){
        //关闭 view渲染
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $this->Redis_= new Redis;
        $this->Redis_->connect('127.0.0.1',6379);
    }

    public function indexAction(){

    }


    public function userAction(){
        $request = $_SERVER;
        //var_dump($request);


        $User=$this->load('user');
        //var_dump($User->show());

        $method = strtolower($request['REQUEST_METHOD']);
        var_dump($method);
        switch($method){
            case 'get':
                var_dump($User->show());
                break;
            case 'post':
                break;
            case 'put':
                break;
            case 'delete':
                break;
            default:
                http_response_code(404);
        }

       /* $User_ = $this->load('user');
        $user = $User_->show();
        var_dump($user);*/
    }

}