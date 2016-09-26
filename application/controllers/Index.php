<?php

class IndexController extends BasicController {
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
    public function httpAction(){
        var_dump($_SERVER);
        // http 遵循 客户端-服务器 模型

        //user_agent 为客户端
    }

    public function testAction(){
        //获取 请求的实例
        $http = $this->getRequest();
        var_dump($http);

        //获取控制器 实例

        echo 'index/test';
        var_dump($http->getServer());
        var_dump($http->get('name'));
        var_dump($http->getCookie());
        var_dump($http->getQuery());
        var_dump($http->getPost());
        var_dump($http->getParams());


        echo $this->get('name');


    }

    public function redis2Action(){
    }

    public function helloAction(){

        //学习 https://github.com/laruence/yaf/blob/master/tests/
       // echo 'hisheng';
       /* if (extension_loaded("yaf")){
            print "yes";

        }*/

      /*  $request  = new Yaf_Request_Simple("CLI", "index", "Controller", 'hello', array("para" => 2));
        var_dump($request);

        echo $request->getControllerName();
        var_dump((bool)$request->setParam("name", "Laruence"));*/





       /* $loader = Yaf_Loader::getInstance(dirname(__FILE__), dirname(__FILE__) . "/global");
        var_dump($loader);
        $loader->registerLocalNamespace("Baidu");
        $loader->registerLocalNamespace("Sina");
        $loader->registerLocalNamespace(array("Wb", "Inf", NULL, array(), "123"));
        var_dump($loader->getLocalNamespace());
        var_dump($loader->isLocalName("Baidu_Name"));

        try {
            var_dump($loader->autoload("Sohu_Name"));
        } catch (Yaf_Exception_LoadFailed $e) {
            var_dump($e->getMessage());
        }*/

       /* $a = array('ss'=>33,'df'=>454);
        Yaf_Registry::set('hi',$a);
        var_dump(Yaf_Registry::get('hi'));

        var_dump(Yaf_Registry::has('hi'));
        var_dump(Yaf_Registry::has('name'));*/

        /*$response = new Yaf_Response_Http();
        var_dump($response);

        $body = 'body';
        $response->appendBody('body');
        $response->response();
        var_dump($response->getBody());
        $response->appendBody('hou');
        var_dump($response->getBody());
        $response->prependBody('qian');
        var_dump($response->getBody());

        var_dump(Yaf_Response_Abstract::DEFAULT_BODY);
        var_dump($response->getBody(NULL));
        var_dump($response->getBody(Yaf_Response_Http::DEFAULT_BODY));
        var_dump($response->getBody());
        $response->response();
        var_dump($response->getBody());*/



       /* $request_uri = 'hi/hello' ;
        $base_uri = 'yaf.zhs.com';

        $request = new Yaf_Request_Http($request_uri,$base_uri);
        var_dump($request);
        unset($base_uri);
        unset($request_uri);

        $route = new Yaf_Route_Static();
        var_dump($route->route($request));
        var_dump($request);*/

        /*$request = new Yaf_Request_Http();
        var_dump($request);

        $request2 = $this->getRequest();
        var_dump($request2);*/

       /* $config=dirname(dirname(__DIR__)).'/conf/application.ini';
        echo $config;
        echo '<br/>';
        $config1 = new Yaf_Config_Ini($config);
        var_dump($config1);
        var_dump($config1->readonly());*/




       /* var_dump(Yaf_Dispatcher::getInstance()->getRouter());

        $router = new Yaf_Router();
        var_dump($router);

        $route = new Yaf_Route_Simple('index','index','hello');
        var_dump($route);
        $sroute = new Yaf_Route_Supervar('r');
        $router->addRoute("simple", $route)->addRoute("super", $sroute);
        var_dump($router);
        var_dump($router->getRoute("simple"));*/




        /*
        var_dump($this->getView());
        $view_dir = dirname(dirname(__DIR__)).'/application/views';
        echo $view_dir;
        $view = new Yaf_View_Simple($view_dir);
        var_dump($view);
        $value = "laruence";
        $view->assign("name", $value);
        var_dump($view);
        var_dump($view->name);
        var_dump($view->noexists);
        */


      /*  $previous = new Yaf_Exception("Previous", 100);
        $exception = new Yaf_Exception("Exception ddd ", 200, $previous);
        var_dump($previous === $exception->getPrevious());
        var_dump($exception->getMessage());
        var_dump($exception->getPrevious()->getCode());*/


        var_dump($_SESSION);
        var_dump($_COOKIE);
        $session = Yaf_Session::getInstance();
        var_dump($session);

        $_SESSION["name"] = "Laruence";
        var_dump($_SESSION);

        $age = 30;
        $session->age = $age;

        var_dump($session);

        var_dump($session->has('name'));
        var_dump($session->get('name'));
        $session->del("name");
        var_dump($session->get('name'));



    }

    public function yafAction(){
        Yaf_Dispatcher::getInstance()->autoRender(true);
        //$this->getView()->assign("html", $html);
    }

}
