<?php
/**
 * Created by PhpStorm.
 * User: 吴宏佳
 * Date: 2018/8/23
 * Time: 下午1:50
 */



function randomStr($len)
{
    $pattern = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ' . time();
    $res = "";
    for ($num = 0; $num < $len; $num++) {
        $res = $res . $pattern[rand(0, strlen($pattern) - 1)];
    }
    return $res;
}
