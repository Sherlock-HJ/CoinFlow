<?php
/**
 * Created by PhpStorm.
 * User: wuhongjia
 * Date: 03/11/2018
 * Time: 10:24
 */


if (!function_exists('json')) {
    /**
     * 获取\think\response\Json对象实例
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