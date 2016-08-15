<?php

class IndexController extends BasicController {

	public function init(){
        $userID = $this->getSession('userID');
	}

	public function indexAction() {
		/*$helloWorld=Yaf_Registry::get("helloWorld");
		print_r($helloWorld);*/
    }

}
