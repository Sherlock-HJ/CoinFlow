<?php

/**
 * Created by PhpStorm.
 * User: 吴宏佳
 * Date: 2018/11/2
 * Time: 上午8:49
 */
require_once BASEPATH . "lib/Db.php";
require_once BASEPATH . "lib/Signature.php";
require_once BASEPATH . "lib/NetWork.php";

class Order
{
    private $db;

    function __construct()
    {
        $db = Db::init();
        $this->db = $db;

        $db->query("CREATE TABLE IF NOT EXISTS `pay_flow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tradnum` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `body` varchar(100) COLLATE utf8_bin DEFAULT NULL COMMENT '商品名称',
  `total_fee` varchar(20) COLLATE utf8_bin DEFAULT '0' COMMENT '总金额',
  `fromuid` int(11) NOT NULL,
  `touid` int(11) NOT NULL,
  `fromusercode` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `tousercode` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `out_trade_no` varchar(50) COLLATE utf8_bin DEFAULT NULL COMMENT '商户订单号',
  `trade_type` varchar(50) COLLATE utf8_bin DEFAULT NULL COMMENT '交易类型',
  `notify_url` varchar(50) COLLATE utf8_bin DEFAULT NULL COMMENT '通知地址',
  `note` varchar(50) COLLATE utf8_bin DEFAULT NULL COMMENT '备注',
  `ctime` int NOT NULL,
  `paystatus` tinyint(2) DEFAULT '0' COMMENT '支付状态 0：未支付 1：支付成功，2：支付失败',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tradnum` (`tradnum`)
  ) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=DYNAMIC;");

        $db->query("CREATE TABLE IF NOT EXISTS `payplat_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appid` varchar(50) COLLATE utf8_bin DEFAULT NULL COMMENT '第三方ID',
  `secret` varchar(50) COLLATE utf8_bin DEFAULT '' COMMENT '第三方密码',
  `cert` varchar(50) COLLATE utf8_bin DEFAULT '' COMMENT '第三方上传公钥文件',
  `uid` int(11) DEFAULT NULL,
  `usercode` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `ctime` int NOT NULL,
  `status` tinyint(4) DEFAULT '0' COMMENT '状态 1：正常 2：失效',
  `secretstr` text COLLATE utf8_bin,
  `appname` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=DYNAMIC;");
    }

    /** 创建订单
     * @param $params
     * @return array|bool|int|string
     */
    function create($params)
    {
        $loob = Check::willPass($params, ["order_body",//当前时间点（秒）
            "uid",//付款人
            "usercode",//付款人
        ]);
        if ($loob !== 1) {
            return $loob;
        }

        $order_bodys = array();
        $order_body = urldecode($params["order_body"]);
        $tmpArr = explode("&", $order_body);

        foreach ($tmpArr as $value) {
            $tmpArr2 = explode("=", $value);
            $order_bodys[$tmpArr2[0]] = $tmpArr2[1];
        }


        $loob = Check::willPass($order_bodys, ["appid",//商户应用ID
            "nonce_str",//随机字符串
            "body",//商品描述
            "out_trade_no",//商户订单号
            "total_fee",//总金额
            "notify_url",//通知地址
            "trade_type",//交易类型
            "sign",//签名
            "note",//备注
            "timestamp",//当前时间点（秒）
        ]);
        if ($loob !== 1) {
            return $loob;
        }
        $appid = $order_bodys["appid"];

        $db = $this->db;
        $que = $db->query("SELECT usercode,uid,cert From payplat_access  WHERE appid='{$appid}' LIMIT 1");

        $certName = null;
        $toUid = null;
        $toUsercode = null;
        if ($row = $db->fetch_assoc($que)) {
            $certName = $row["cert"];
            $toUsercode = $row["usercode"];
            $toUid = $row["uid"];
        } else {
            return ["error_info" => "无此商家"];
        }

        $signObj = new Signature();
        $loob = $signObj->deSign($order_bodys, $certName);
        if ($loob === 0) {
            return ["error_info" => "签名不正确"];
        }
        if ($loob === -1) {
            return ["error_info" => "签名错误"];
        }

        $tradnum = "123dfe";

        $sql = sprintf("INSERT INTO pay_flow (note,trade_type,notify_url,out_trade_no,ctime,total_fee,touid, tousercode, fromuid, fromusercode ,body, tradnum, paystatus) VALUE ('%s','%s','%s','%s',%d,%d,%d,'%s',%d,'%s','%s','%s',%d)"
            , $order_bodys["note"], $order_bodys["trade_type"], $order_bodys["notify_url"], $order_bodys["out_trade_no"], time(), $order_bodys["total_fee"], $toUid, $toUsercode, "1", "wo", $order_bodys["body"], $tradnum, 0);
        if ($db->query($sql)) {
            return ["tradnum" => $tradnum];
        }
        return ["失败"];
    }

    /** 支付
     * @param $params
     */
    function pay($params)
    {
        $loob = Check::willPass($params, ["pwd",//转账密码
            "nonce_str",//随机字符串
            "body",//商品描述
            "out_trade_no",//商户订单号
            "total_fee",//总金额
            "notify_url",//通知地址
            "trade_type",//交易类型
            "sign",//签名
            "note",//备注
            "timestamp",//当前时间点（秒）
        ]);
        if ($loob !== 1) {
            return $loob;
        }

        $url = "https://192.168.113.107:8085/api";
        $net = new NetWork();
        $paramsPOST = [];
        $paramsPOST["orgId"] = "153922337400001";
        $paramsPOST["f"] = "trans";

        $p = [];
        $p["from"] = "9072000000153922344546769";
        $p["to"] = "9072000000153922506995820";
        $p["psw"] = "22";
        $p["money"] = "100";
        $p["desc"] = "a";

        $paramsPOST["p"] = json_encode($p);

        return $net->post("http://192.168.113.107:8085/api", $paramsPOST);
    }

    function de($params)
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

        $signObj = new Signature();
        $url = $signObj->enSign($params, BASEPATH . "crt/mxapi.key");
        $url  = urlencode($url);
        echo $url;
        return 1;

    }

    function test()
    {
        $net = new NetWork();
        return $net->get("https://www.baidu.com", []);

    }
}