<?php
/**
 * Created by PhpStorm.
 * User: wuhongjia
 * Date: 03/11/2018
 * Time: 10:24
 */


if (!function_exists('xml')) {
    /**
     * 获取Response对象实例
     * @param mixed   $data 返回的数据
     * @param integer $code 状态码
     * @param array   $header 头部
     * @param array   $options 参数
     * @return Response
     */
    function xml($data = [], $code = 200, $header = [], $options = [])
    {
        include_once BASEPATH."lib/Response.php";
        return new Response($data,'xml', $code, $header, $options);
    }
}

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
        return new Response($data,'json', $code, $header, $options);
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

        return new Response($data, 'json',403, $header, $options);
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


if (!function_exists('postCoinOS')) {
    /** 向积分系统发送请求
     * @param $params
     * @return Response
     */
    function postCoinOS($params,$directly = true)
    {

        include_once BASEPATH."lib/NetWork.php";

        // TODO  配置 orgId
        $params["orgId"] = "154140269500001";
        $url = "http://ykcoin.quasend.com:8085/api";
        if (DEBUG){
            // TODO  配置 orgId
            $params["orgId"] = "154406026600001";
            $url = "http://139.196.9.180:18085/api";
        }
        $res = NetWork::post($url,$params);


        if ($directly){
            if ($res){
                if ($res->st === 1 ){
                    return json($res->result);
                }else{
                    return error($res->msg);
                }
            }else{
                return error(["info"=>"CoinOS返回错误","error"=>[$params,$res]]);
            }
        }else{
            return $res;
        }

    }

}