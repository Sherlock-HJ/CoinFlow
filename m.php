<?php
/**
 * Author：helen
 * CreateTime: 2016/07/27 10:26
 * Description：
 */
// 权限控制
//include_once './auth.php';

session_start();

$lastTime = $_SESSION["last_time"];
if (empty($lastTime)|| time()- $lastTime >10){
    $_SESSION["last_time"] = time();

}else{
    header("HTTP/1.0 600 Business errors");
    die("接口重复调用");
}



// 项目根路径
define('BASEPATH', dirname(__FILE__) . "/");
// 调试模式
define('DEBUG', true);

//TODO 日志

require_once BASEPATH . "lib/config.php";
require_once BASEPATH . "lib/log.php";
require_once BASEPATH . "lib/Check.php";

// 应用入口文件
date_default_timezone_set("PRC");
header('Content-type: text/html;charset=utf-8');
if (DEBUG) {
    header("Access-Control-Allow-Origin: *");
}

if (empty($_SERVER["PATH_INFO"])){
    die("hello");
}
$path_info = $_SERVER["PATH_INFO"];
$classPath = dirname($path_info);
$funcName = basename($path_info);

$filename = BASEPATH . "api" . $classPath . ".php";
if (!file_exists($filename)) {
    die("0路径不正确");

}
include_once $filename;

$className = basename($classPath);
if (!class_exists($className)) {
    die("1路径不正确");

}

$object = new $className;

if (!method_exists($object, $funcName)) {

    die("2路径不正确");
}

$params = array();
foreach ($_REQUEST as $key => $value) {
    if (!empty($value)) {
        $params[$key] = $value;
    }
}

$result = call_user_func(array($object, $funcName), $params);

if (!is_array($result)){
    if (DEBUG){
        die ($result);

    }else{
        die("响应结果不是json") ;
    }

}
if ($result) {
    if (empty($result["error_code"])&&empty($result["error_info"])) {
        echo json_encode($result);

    } else {
        header("HTTP/1.0 600 Business errors");
        if (DEBUG){
            $result["root_path"] = BASEPATH;
        }
        echo json_encode($result);
    }
} else {
    header("HTTP/1.0 600 Business errors");

    echo "数据错误";
}