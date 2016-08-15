<?php
/**
 * Created by PhpStorm.
 * User: zhanghaisheng
 * Date: 2016/8/15
 * Time: 20:46
 */

namespace HttpFoundation;

class Request
{
    public function get()
    {
        echo "I am going to send a GET request";
        exit;
    }
}