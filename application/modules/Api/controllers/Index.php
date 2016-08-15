<?php

class IndexController extends BasicController { 
	 
	public function indexAction(){
	    Yaf_Dispatcher::getInstance()->autoRender(FALSE);		 
		echo "string";
	}

	 
	
}