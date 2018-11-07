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


if (!function_exists('postCoinOS')) {
    /** 向积分系统发送请求
     * @param $params
     * @return Response
     */
    function postCoinOS($params)
    {
        include_once BASEPATH."lib/NetWork.php";

        $net = new NetWork();
        // TODO  配置 orgId
        $params["orgId"] = "153922337400001";
        $url = "https://ykcoin.quasend.com:8085/api";
        $res = $net->post($url,$params);

        if ($res){
            if ($res->st === 1 ){
                return json($res->result);
            }else{
                return error($res->msg);
            }
        }else{
            return error(["info"=>"CoinOS返回错误","error"=>$res]);
        }
    }

}