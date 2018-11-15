<?php

/**
 * Created by PhpStorm.
 * User: 吴宏佳
 * Date: 2018/11/1
 * Time: 下午2:25
 */
class Signature
{
    /**
     * 加签
     * @param $params
     * @param $privateName
     * @return string
     */
    function enSign($params, $privateName)
    {
        $priv_key_id = openssl_get_privatekey(file_get_contents($privateName));

        $paramsStr = $this->create_linkstring($this->arg_sort($this->para_filter($params)));
        openssl_sign($paramsStr, $sign, $priv_key_id, OPENSSL_ALGO_SHA256);
        openssl_free_key($priv_key_id);

        $sign = base64_encode($sign);
//        $sign = urlencode($sign);

        return "{$paramsStr}&sign={$sign}";

    }

    /**
     * 验签
     * @param $params
     * @param $certName
     * @return bool
     */
    function deSign($params, $certName)
    {
        $pub_key_id = openssl_get_publickey(file_get_contents($certName));

        $paramsStr = $this->create_linkstring($this->arg_sort($this->para_filter($params)));

        $sign = base64_decode($params["sign"]);// base64解密

        $loob = openssl_verify($paramsStr, $sign, $pub_key_id, OPENSSL_ALGO_SHA256);
        openssl_free_key($pub_key_id);
        return $loob;
    }


    /**把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * $array 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    private function create_linkstring($array)
    {
        $arg = "";
        while (list ($key, $val) = each($array)) {
            $arg .= $key . "=" . $val . "&";
        }
        $arg = substr($arg, 0, count($arg) - 2);             //去掉最后一个&字符
        return $arg;
    }


    /**除去数组中的空值和签名参数
     * $parameter 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    private function para_filter($parameter)
    {
        $para = array();
        while (list ($key, $val) = each($parameter)) {
            if ($key == "sign" || $key == "sign_type" || $val == "") continue;
            else    $para[$key] = $parameter[$key];
        }
        return $para;
    }


    /**对数组排序
     * $array 排序前的数组
     * return 排序后的数组
     */
    private function arg_sort($array)
    {
        ksort($array);
        reset($array);
        return $array;
    }
}