<?php
/**
 * Created by PhpStorm.
 * User: 吴宏佳
 * Date: 2018/8/27
 * Time: 下午3:52
 */

// 调试模式
define('DEBUG', true);

//设置时区 为 中国
date_default_timezone_set("PRC");

function BoinOSBaseURL(){
    if (DEBUG){
        return "https://192.168.113.107:8085/api";
    }
    return "";
}

if (DEBUG) {
    // 跨域
    header("Access-Control-Allow-Origin: *");
}



