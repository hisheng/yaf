<?php
/**
 * Created by PhpStorm.
 * User: zhanghaisheng
 * Date: 2016/9/1
 * Time: 10:20
 */

class UserController extends BasicController{
    protected static $User_ ;

    protected  function init(){
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        self::$User_=$this->load('user');
        //header("Access-Control-Allow-Origin: *");
    }

    public function indexAction($p = ''){


        $request = $_SERVER;

        //$User=$this->load('user');
        //var_dump($User->show());

        $method = strtolower($request['REQUEST_METHOD']);
        //var_dump($method);

        switch($method){
            case 'get':
                $this->getuser();
                break;
            case 'post':
                $username=$this->getPost('username');
                $this->adduser($username);
                break;
            case 'put':
                $this->putuser();
                break;
            case 'delete':
                $this->deleteuser();
                break;
            default:
                http_response_code(404);
        }
    }

    public function showAction(){
        $User=$this->load('user');
        var_dump($User->show());

        $redis=new Redis();
        $redis->connect('127.0.0.1',6379);
        var_dump($redis->zRevRange('userdetail::4',0,-1));
    }

    public function getuser(){
        print_r(json_encode(self::$User_->show()));
    }

    public function adduser($username){
        print_r(json_encode(array('add ok '.$username)));
    }

    public function putuser(){
        //$data =array('ss');
       // print_r(json_encode($data));exit;

        //print_r(json_encode($this->getRequest()));exit;
        $username=$this->getData('username');
        print_r(json_encode($username));
    }
    public function deleteuser(){
        $username=$this->getData('username');
        print_r(json_encode($username));
    }


    //下面是  正常的写法
    public function getAction(){
        $this->getuser();
    }
    public function addAction(){
        $username=$this->getPost('username');
        $this->adduser($username);
    }

    public function putAction(){
        $this->putuser();
    }
    public function deleteAction(){
        $this->deleteuser();
    }

}