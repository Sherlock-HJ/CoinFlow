<?php
/**
 * Created by PhpStorm.
 * User: wuhongjia
 * Date: 04/11/2018
 * Time: 20:35
 */

class Test
{
    function test1($params)
    {

        $loob = Check::willPass($params, ["appid",//商户应用ID
            "nonce_str",//随机字符串
            "body",//商品描述
            "out_trade_no",//商户订单号
            "total_fee",//总金额
            "notify_url",//通知地址
            "trade_type",//交易类型
            "sign",//签名
            "note",//备注
            "timestamp",//当前时间点（秒）
            "uid",//付款人
            "usercode",//付款人
        ]);
        if ($loob !== 1) {
            return $loob;
        }
        return error("dfdfd");

    }
}