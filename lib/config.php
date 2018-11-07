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

//积分系统的积分类型
define('COIN_CODE', "MRY");


if (DEBUG) {
    // 跨域
    header("Access-Control-Allow-Origin: *");
}



