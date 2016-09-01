<?php
/**
 * Created by PhpStorm.
 * User: zhanghaisheng
 * Date: 2016/9/1
 * Time: 13:56
 */


//require dirname(dirname(dirname(__DIR__))).'/application/controllers/Index.php';



class IndexTest extends \PHPUnit_Framework_TestCase{

    public function testHello(){
        $request = new Yaf_Request_Simple("CLI", "Index", "Index", 'hello');

        var_dump($request);
    }
}