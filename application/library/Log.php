<?php
/**
 * Created by PhpStorm.
 * User: zhanghaisheng
 * Date: 2016/8/31
 * Time: 15:26
 */
namespace Library;




class Log{
    public static  function test(){
        $log = new \Monolog\Logger('name');
        $log->pushHandler(new \Monolog\Handler\StreamHandler('logs/your.log', \Monolog\Logger::WARNING));

// add records to the log
        $log->warning('Foo');
        $log->error('Bar');



        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);
        $isShowAD=$redis->hGet('runad', 'isShowAD');
        var_dump($isShowAD);

    }
}
// create a log channel
