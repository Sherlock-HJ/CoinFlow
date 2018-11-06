<?php
/**
 * Created by PhpStorm.
 * User: wuhongjia
 * Date: 04/11/2018
 * Time: 20:35
 */

require_once BASEPATH . "lib/Signature.php";

class Test
{
    function en($params)
    {
        $loob = Check::willPass($params, ["appid",//商户应用ID
            "nonce_str",//随机字符串
            "body",//商品描述
//            "out_trade_no",//商户订单号
//            "total_fee",//总金额
//            "notify_url",//通知地址
//            "trade_type",//交易类型
//            "note",//备注
//            "timestamp",//当前时间点（秒）
        ]);
        if ($loob !== 1) {
            return $loob;
        }

        $signObj = new Signature();
        $url = $signObj->enSign($params, BASEPATH . "crt/mxapi.key");
//        $url = urlencode($url);

        echo $url;
//        $net = new NetWork();
//
//        $signObj = new Signature();
//        $url = $signObj->deSign($this->deOrderBody($url), BASEPATH . "crt/mxapi.crt");
//
//        echo $url?"成功":"失败";
//        echo PHP_EOL;
//        echo "whj   ";
//
//        echo "123";
//        echo $url;
//        echo "123";
//
//        echo PHP_EOL;
        return json(1);

    }
    function  de($params){
        $signObj = new Signature();
        $url = $signObj->deSign($params, BASEPATH . "crt/mxapi.crt");
//        $url = urlencode($url);

        echo $url;
//        $net = new NetWork();
//
//        $signObj = new Signature();
//        $url = $signObj->deSign($this->deOrderBody($url), BASEPATH . "crt/mxapi.crt");
//
//        echo $url?"成功":"失败";
//        echo PHP_EOL;
//        echo "whj   ";
//
//        echo "123";
//        echo $url;
//        echo "123";
//
//        echo PHP_EOL;
        return json($params);
    }
}