<?php
/**
 * Created by PhpStorm.
 * User: 吴宏佳
 * Date: 2018/8/27
 * Time: 下午3:52
 */

// 调试模式
define('DEBUG', true);

//返回字段
define('ERROR_INFO','error_info');
define('ERROR_CODE','error_info');



//设置时区 为 中国
date_default_timezone_set("PRC");
//设置 响应头
header('Content-type: text/html;charset=utf-8');

if (DEBUG) {
    // 跨域
    header("Access-Control-Allow-Origin: *");
}

