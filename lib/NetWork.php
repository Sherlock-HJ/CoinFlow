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



    /**
     * @param string $url
     * @param array $params 参数数组
     * @return mixed
     */
    public static function get($url, array $params)
    {

        $ch = self::init($url);

        $url = $url . "?" . self::query($params);

        curl_setopt($ch, CURLOPT_URL, $url);

        return self::output($ch);
    }

    /**
     * @param $url
     * @param string|array $data 参数数组/URL-encoded字符串。 要发送文件，在文件名前面加上@前缀并使用完整路径。
     * @param string $codeType
     * @return mixed
     */
    public static function post($url, $data, $codeType = "x-www-from-urlencoded")
    {

        $ch = self::init($url);

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

        if ($codeType === "x-www-from-urlencoded"){
            $data = self::query($data);
        }elseif ($codeType === "form-data"){

        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        curl_setopt($ch, CURLOPT_URL, $url);

        return self::output($ch);

    }


    /**
     * @param array $params
     * @return string
     */
    private static function query(array $params)
    {
        $p = '';
        foreach ($params as $k => $v) {
            $p .= empty($p) ? '' : '&';
            if (is_array($v)){
                foreach ($v as $k1 => $v1){
                    $v[$k1] = strval($v1);
                }
                $v = json_encode($v);

            }

            $p .= urlencode($k) . "=" . urlencode($v);
//            $p .= $k . "=" . $v;
        }
        return $p;
    }
    private static function output($ch)
    {
        $str = curl_exec($ch);

        self::dealloc($ch);

        $obj = json_decode($str);
        if (json_last_error() == JSON_ERROR_NONE){
            return $obj;
        }
        return $str;
    }
    private static function init($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, false);


        if (stripos($url,'https://')===0){
            //HTTPS 认证
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_CAINFO, BASEPATH . 'crt/ca.crt');
            curl_setopt($ch, CURLOPT_SSLCERT, BASEPATH . 'crt/coinapi.crt');
            curl_setopt($ch, CURLOPT_SSLKEY, BASEPATH . 'crt/coinapi.key');
        }


        return $ch;
    }

    private static function dealloc($ch)
    {

        echo curl_error($ch);

        curl_close($ch);

    }

}