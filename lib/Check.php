<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/2
 * Time: 10:25
 */

class Check
{
    /**
     * @param array $params 需要验证的参数键值对数组
     * @param array $checks 必填的参数数组
     * @return int|string 返回1 正常
     */
    public static function willPass($params,$checks)
    {
        $loob = 1;
        foreach ($checks as $value){
            if (empty($params[$value])) {

                return error(2000,"缺少参数: ".$value);
            }
        }
        return $loob;
    }
}