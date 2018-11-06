<?php
/**
 * Created by PhpStorm.
 * User: wuhongjia
 * Date: 03/11/2018
 * Time: 10:24
 */


if (!function_exists('json')) {
    /**
     * 获取Response对象实例
     * @param mixed   $data 返回的数据
     * @param integer $code 状态码
     * @param array   $header 头部
     * @param array   $options 参数
     * @return Response
     */
    function json($data = [], $code = 200, $header = [], $options = [])
    {
        include_once BASEPATH."lib/Response.php";
        return new Response($data, $code, $header, $options);
    }
}

if (!function_exists('error')) {
    /**
     * 获取Response对象实例
     * @param mixed   $data 返回的数据
     * @param integer $code 状态码
     * @param array   $header 头部
     * @param array   $options 参数
     * @return Response
     */

    function error($info = "", $code =null ,  $header = [], $options = [])
    {
        include_once BASEPATH."lib/Response.php";

        $data = [];
        if ($code != null){
            $data["error_code"] = $code;
        }
        $data["error_info"] = $info;

        return new Response($data, 403, $header, $options);
    }
}

if (!function_exists('isResponse')) {
    /**  对象 是否是 Response 类
     * @param $obj
     * @return bool
     */
    function isResponse($obj)
    {
        return is_object($obj) && get_class($obj) === 'Response';
    }
}