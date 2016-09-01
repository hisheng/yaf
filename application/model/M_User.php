<?php
/**
 * Created by PhpStorm.
 * User: zhanghaisheng
 * Date: 2016/9/1
 * Time: 10:26
 */

class M_User extends Model{
     function __construct(){
        $this->table = 'users';
        parent::__construct();
    }

    public function show(){
        return $this->Where('uid','=',23)->SelectOne();
    }


}