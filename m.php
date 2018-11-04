<?php
/**
 * Author：helen
 * CreateTime: 2016/07/27 10:26
 * Description：
 */

// 应用入口文件

// 权限控制
//include_once './auth.php';

session_start();

$lastTime = $_SESSION["last_time"];
if (empty($lastTime) || time() - $lastTime > 1) {
    $_SESSION["last_time"] = time();

} else {
    http_response_code(403);
    die("接口重复调用");
}


// 项目根路径
define('BASEPATH', dirname(__FILE__) . "/");

include_once BASEPATH . "lib/config.php";

if (empty($_SERVER["PATH_INFO"])) {
    die("hello");
}

$path_info = $_SERVER["PATH_INFO"];
$classPath = dirname($path_info);
$funcName = basename($path_info);

$filename = BASEPATH . "api" . $classPath . ".php";
if (!file_exists($filename)) {
    http_response_code(404);
    die("路径不正确");
}

include_once $filename;

$className = basename($classPath);
if (!class_exists($className)) {
    http_response_code(404);
    die("路径不正确");
}

$object = new $className;

if (!method_exists($object, $funcName)) {
    http_response_code(404);
    die("路径不正确");
}


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
if (is_object($result)&& get_class($result) === 'Response') {

    $result->send();

} else {
    http_response_code(500);
    echo "请用helper.php 文件里的 json() 方法返回数据！";
}

//TODO 加日志
include_once BASEPATH . "lib/log1.php";
