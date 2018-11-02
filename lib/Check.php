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
                $loob = ["error_code"=>2000,"error_info"=>"缺少参数: ".$value];
                break;
            }
        }
        return $loob;
    }
}