<?php
/**
 * Created by PhpStorm.
 * User: wuhongjia
 * Date: 04/11/2018
 * Time: 20:35
 */

// 应用入口文件

// 项目根路径
define('BASEPATH', dirname(__FILE__) . "/");

if (empty($_POST["method"])) {
    die("hello");
}

$pathArr = explode(".",$_POST["method"]);
unset($_POST["method"]);
$className = $pathArr[1];
$classPath = $pathArr[0]."/controller/".$className;
$funcName = $pathArr[2];

$filename = BASEPATH . "api/" . $classPath . ".php";
if (!file_exists($filename)) {
    http_response_code(404);
    die("方法不存在");
}

include_once $filename;

if (!class_exists($className)) {
    http_response_code(404);
    die("方法不存在");
}
include_once BASEPATH . "lib/config.php";
$object = new $className;

if (!method_exists($object, $funcName)) {
    http_response_code(404);
    die("方法不存在");
}

/////接口调用限制 1秒钟调用一次
//session_start();
//if (empty( $_SESSION[$_SERVER["REQUEST_URI"]])){
//    $_SESSION[$_SERVER["REQUEST_URI"]] = 1;
//}
//$lastTime = $_SESSION[$_SERVER["REQUEST_URI"]];
//if (!empty($lastTime) && time() - $lastTime < 1) {
//    http_response_code(403);
//    die("接口重复调用");
//} else {
//    $_SESSION[$_SERVER["REQUEST_URI"]] = time();
//}


//参数过滤
$params = array();
foreach ($_POST as $key => $value) {
    if (!empty($value)) {
        $params[$key] = $value;
    }
}

include_once BASEPATH . "lib/Check.php";
include_once BASEPATH . "lib/helper.php";

//执行类中的方法

$result = call_user_func(array($object, $funcName), $params);

//    instanceof
if (!(!is_null($result) && isResponse($result))) {
    http_response_code(500);
    echo "请用helper.php 文件里的 json()/error() 方法返回数据！";

}

$result->send();


//TODO 加日志
//include_once BASEPATH . "lib/Log.php";
//if (http_response_code() == 200){
//    Log::log($result->getContent());
//}else{
//    Log::error($result->getContent());
//}

