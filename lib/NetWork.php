<?php

/**
 * Created by PhpStorm.
 * User: 吴宏佳
 * Date: 2018/11/1
 * Time: 上午10:32
 */

/**
 * Class NetWork
 */
class NetWork
{

    private $ch;


    function __construct()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HEADER, false);

        //HTTPS 认证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CAINFO, BASEPATH.'crt/ca.crt');
        curl_setopt($ch, CURLOPT_CAINFO, BASEPATH.'crt/server.crt');
        curl_setopt($ch, CURLOPT_SSLCERT, BASEPATH.'crt/coinapi.crt');
        curl_setopt($ch, CURLOPT_SSLKEY, BASEPATH.'crt/coinapi.key');

        $this->ch = $ch;
    }
    function __destruct()
    {
        $ch = $this->ch;

        echo curl_error($ch);

        curl_close($ch);

    }

    /**
     * @param string $url
     * @param array $params 参数数组
     * @return mixed
     */
    function get($url, array $params)
    {

        $ch = $this->ch;

        curl_setopt($ch, CURLOPT_URL, $url . "?" . $this->query($params));

        return curl_exec($ch);
    }

    /**
     * @param string $url
     * @param string|array $data  参数数组/URL-encoded字符串。 要发送文件，在文件名前面加上@前缀并使用完整路径。
     * @return mixed
     */
    function post($url, $data)
    {

        $ch = $this->ch;

        curl_setopt($ch, CURLOPT_POST, 1);

//        $headers[] = "Content-type: text/plain";
//        $headers[] = "Content-type: application/x-www-from-urlencoded";
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);//定义请求类型
        /**
         * 传递一个数组到 CURLOPT_POSTFIELDS，
         * cURL会把数据编码成 multipart/form-data，
         * 而然传递一个URL-encoded字符串时，
         * 数据会被编码成 application/x-www-form-urlencoded。
         * */
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        curl_setopt($ch, CURLOPT_URL, $url);


        return curl_exec($ch);

    }


    /**
     * @param array $params
     * @return string
     */
    function query(array $params)
    {
        $p = '';
        foreach ($params as $k => $v) {
            $p .= empty($p) ? '' : '&';
            $p .= urlencode($k) . "=" . urlencode($v);
        }
        return $p;
    }

}