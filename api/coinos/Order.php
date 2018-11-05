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
  `tradnum` varchar(50) COLLATE utf8_bin DEFAULT NULL COMMENT '单号',
  `body` varchar(100) COLLATE utf8_bin DEFAULT NULL COMMENT '商品信息',
  `total_fee` varchar(20) COLLATE utf8_bin DEFAULT '0' COMMENT '总金额',
  `fromuid` int(11) NOT NULL,
  `touid` int(11) NOT NULL,
  `fromcard` varchar(50) NOT NULL COMMENT '卡号',
  `tocard` varchar(50) NOT NULL,
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

    /** 拆分单子 URL query string
     * @param string $orderBody urlencode过的string
     * @return array
     */
    private function deOrderBody($orderBody)
    {
        $orderBodys = array();
        $orderBody = urldecode($orderBody);
        $tmpArr = explode("&", $orderBody);

        foreach ($tmpArr as $value) {
            $tmpArr2 = explode("=", $value);
            $orderBodys[$tmpArr2[0]] = $tmpArr2[1];
        }
        return $orderBodys;
    }

    /** 验签
     * @param $orderBodys
     * @param $certName
     * @return int|Response
     */
    private function deSign($orderBodys, $certName)
    {

        $signObj = new Signature();
        $loob = $signObj->deSign($orderBodys, $certName);
        if ($loob === 0) {
            return error("签名不正确");
        }
        if ($loob === -1) {
            return error("签名错误");
        }
        return 1;
    }

    /**  验证商家
     * @param $orderBodys
     * @return array|int|Response|string
     */
    private function deMerchants($orderBodys)
    {

        $loob = Check::willPass($orderBodys, ["appid",//商户应用ID
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
        $appid  = $orderBodys["appid"];
        $db = $this->db;
        $que = $db->query("SELECT usercode,uid,cert From payplat_access  WHERE appid='{$appid}' LIMIT 1");

        $certName = null;
        $toUid = null;
        $toUsercode = null;
        if ($row = $db->fetch_assoc($que)) {
            $db->free_result($que);
            return $row;

        } else {
            return error("无此商家");
        }
    }

    /** 创建订单
     * @param $params
     * @return array|bool|int|string
     */
    function create($params)
    {
        $loob = Check::willPass($params, ["order_body",//单子信息
            "uid",//付款人
            "usercode",//付款人
        ]);
        if ($loob !== 1) {
            return $loob;
        }
        $fromUid = $params["uid"];
        $fromUsercode = $params["usercode"];

        //验 商家
        $orderBodys = $this->deOrderBody($params["order_body"]);
        $row = $this->deMerchants($orderBodys);
        if (is_array($row)) {
            $certName = $row["cert"];
            $toUsercode = $row["usercode"];
            $toUid = $row["uid"];
            if ($fromUid == $toUid || $fromUsercode == $toUsercode) {
                return error("不可以与自身交易");
            }
        } else {
            return $row;
        }

        //验签
        $loob = $this->deSign($orderBodys, $certName);
        if ($loob != 1) {
            return $loob;
        }

        // 生成单号  存入表中
        $tradnum = date("YmdHis") . mt_rand(100, 999) . mt_rand(100, 999);
        /// TODO 请求用户系统 获得用户对应的卡片
        $toCard = "9072000000153922506995820";
        $fromCard = "9072000000153922344546769";

        $sql = sprintf("INSERT INTO pay_flow (note,trade_type,notify_url,out_trade_no,ctime,total_fee,tocard,touid, tousercode, fromcard, fromuid, fromusercode ,body, tradnum, paystatus) VALUE ('%s','%s','%s','%s',%d,%d,'%s',%d,'%s','%s',%d,'%s','%s','%s',%d)"
            , $orderBodys["note"], $orderBodys["trade_type"], $orderBodys["notify_url"], $orderBodys["out_trade_no"], time(), $orderBodys["total_fee"], $toCard, $toUid, $toUsercode, $fromCard, $fromUid, $fromUsercode, $orderBodys["body"], $tradnum, 0);
        if ($this->db->query($sql)) {
            return json(["tradnum" => $tradnum]);
        }
        return error("失败");
    }

    /** 支付
     * @param $params
     */
    private function pay($params)
    {
        $loob = Check::willPass($params, ["pwd",//转账密码
            "tradnum"//单号
        ]);
        if ($loob !== 1) {
            return $loob;
        }

        $db = $this->db;
        $sql = sprintf("SELECT * From pay_flow  WHERE tradnum='%s' LIMIT 1", $params["tradnum"]);
        $que = $db->query($sql);

        $row = $db->fetch_assoc($que);
        if (empty($row)) {
            return error("无此单");
        }

        $paramsPOST = [];
        $paramsPOST["orgId"] = "153922337400001";
        $paramsPOST["f"] = "trans";

        $p = [];
        $p["from"] = $row["fromcard"];
        $p["to"] = $row["tocard"];
        $p["psw"] = $params["pwd"];
        $p["money"] = $row["total_fee"];
        $p["desc"] = $row["note"];

        $paramsPOST["p"] = json_encode($p);

        $net = new NetWork();
        echo $net->post(BoinOSBaseURL(), $paramsPOST);
        echo PHP_EOL;

        // TODO 发送成交消息
        return json(["1"]);
    }

    /** 分账（如：商家想退款）
     * @param $params
     */
    function fashionable($params)
    {
        $loob = Check::willPass($params, ["order_body",//单子信息
            "uid",//付款人  多人用英文逗号隔开
            "usercode",//付款人  多人用英文逗号隔开
            "pwd",//转账密码
        ]);
        if ($loob !== 1) {
            return $loob;
        }

        $fromUids = explode(",", $params["uid"]);
        $fromUsercodes = explode(",", $params["usercode"]);
        if (count($fromUids) != count($fromUsercodes)) {
            return error("uid 与 usercode 数量不一致");
        }


//        foreach ($fromUids as $fromUid){
//            if ($fromUid == $toUid ){
//                return error("不可以与自身交易");
//            }
//        }
//        foreach ($fromUsercodes as $fromUsercode){
//            if ( $fromUsercode == $toUsercode){
//                return error("不可以与自身交易");
//            }
//        }


        return json("成功");
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
        $url = urlencode($url);
        echo $url;
        return 1;

    }


}