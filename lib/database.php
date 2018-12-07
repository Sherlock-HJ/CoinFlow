<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

$username = "whjrencaidev";
$password = "R6KAwtS3";
$hostname = "test.intranet.mysql.db.quasend.com:3306";
$database = "rencaidev";

if (DEBUG){
//    $username = "root";
//    $password = "ubuntu";
//    $hostname = "192.168.113.107";
//    $database = "CoinFlow";
    $username = "whjrencaidev";
    $password = "R6KAwtS3";
    $hostname = "test.intranet.mysql.db.quasend.com:3306";
    $database = "rencaidev";
}

return [
    // 服务器地址
    'hostname'        => $hostname,
    // 数据库名
    'database'        => $database,
    // 用户名
    'username'        => $username,
    // 密码
    'password'        => $password,
    // 端口
    'hostport'        => '',
    // 连接dsn
    'dsn'             => '',
    // 数据库连接参数
    'params'          => [],
    // 数据库编码默认采用utf8
    'charset'         => 'utf8'

];
