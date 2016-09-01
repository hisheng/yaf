<?php

error_reporting(E_ALL ^E_NOTICE);
date_default_timezone_set('Asia/Shanghai');


define(LIB_PATH,APP_PATH.'/application/library');
define(CORE_PATH,APP_PATH.'/application/library/core');
define(FUNC_PATH,APP_PATH.'/function');
define(MODEL_PATH,APP_PATH.'/application/model');


define('ENV', strtoupper(ini_get('yaf.environ')));



switch(ENV) {
    case 'DEV':
        ini_set('display_errors', 'on');

        $SERVER_DOMAIN = 'http://dev.yof.com';
        $STATIC_DOMAIN = 'http://devStatic.yof.com';
        $IMG_DOMAIN    = 'http://devImg.yof.com';
        break;

    case 'TEST':
        $logFile = APP_PATH.'/log/php/'.CUR_DATE.'.log';

        ini_set('display_errors', 'off');
        ini_set('log_errors', 'on');
        ini_set('error_log', $logFile);

        $SERVER_DOMAIN = 'http://test.yof.com';
        $STATIC_DOMAIN = 'http://testStatic.yof.com';
        $IMG_DOMAIN    = 'http://testImg.yof.com';
        break;

    case 'PRODUCT':
        $logFile = APP_PATH.'/log/php/'.CUR_DATE.'.log';

        ini_set('display_errors', 'off');
        ini_set('log_errors', 'on');
        ini_set('error_log', $logFile);

        $SERVER_DOMAIN = 'http://yof.mylinuxer.com';
        $STATIC_DOMAIN = 'http://static.yof.com';
        $IMG_DOMAIN    = 'http://img.yof.com';
        break;
}
