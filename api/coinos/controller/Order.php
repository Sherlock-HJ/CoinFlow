<?php

/**
 * Created by PhpStorm.
 * User: 吴宏佳
 * Date: 2018/11/2
 * Time: 上午8:49
 */
require_once BASEPATH . "lib/Db.php";
require_once BASEPATH . "lib/Signature.php";

class Order
{
    private $db;

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
            return error("签名不正确", $orderBodys);
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
            "body",//商品描述              多个用英文逗号隔开
            "out_trade_no",//商户订单号   多个用英文逗号隔开
            "total_fee",//总金额           多个用英文逗号隔开
            "trade_type",//交易类型        多个用英文逗号隔开
            "note",//备注                   多个用英文逗号隔开
            "notify_url",//通知地址
            "sign",//签名
            "nonce_str",//随机字符串
            "timestamp",//当前时间点（秒）
        ]);
        if ($loob !== 1) {
            return $loob;
        }

        $appid = $orderBodys["appid"];
        if ($appid === "muxin" && empty($orderBodys["tousercode"])){
            return error("缺少收款方tousercode");
        }
        $outTradeNo = $orderBodys["out_trade_no"];

        $db = $this->db;
        $que = $db->query("SELECT usercode,uid,cert From payplat_access  WHERE appid='{$appid}' LIMIT 1");


        if ($row = $db->fetch_assoc($que)) {
            $db->free_result($que);

            //  商户单号验证重复
            $usercode = $row["usercode"];
            $sql = "SELECT id From pay_flow  WHERE tousercode='{$usercode}' AND out_trade_no='{$outTradeNo}' LIMIT 1";
            $que = $db->query($sql);
            if ($row_1 = $db->fetch_assoc($que)) {
                $db->free_result($que);
                return error("商户订单号重复");
            }
            if ($appid === "muxin"){
                $row["usercode"] = $orderBodys["tousercode"];

            }
            return $row;

        } else {
            return error("无此商家");
        }
    }

    /** 分页
     * @param $sql
     * @return Response|string
     */
    private function period($sql, $params)
    {
        if (empty($params["since_id"])) {
            if (empty($params["max_id"])) {

            } else {
                $sql .= sprintf(" AND id < '%d' ", $params["max_id"]);
            }
        } else {
            if (empty($params["max_id"])) {
                $sql .= sprintf(" AND id > '%d' ", $params["since_id"]);

            } else {
                if (intval($params["since_id"]) >= intval($params["max_id"])) {
                    return error("since_id 须小于 max_id");
                }
                $sql .= sprintf(" AND id > '%d' AND id < '%d' ", $params["since_id"], $params["max_id"]);

            }
        }
        $sql .= " ORDER BY id DESC ";
        if (empty($params["count"])) {
            $sql .= " LIMIT 10";

        } else {
            $sql .= " LIMIT " . $params["count"];
        }
        return $sql;
    }

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
  `notify_status` tinyint(2) DEFAULT '0' COMMENT '通知状态 0：失败 1：成功',
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

        $certName = null;
        $toUid = null;
        $toUsercode = null;
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
        if ($loob !== 1) {
            return $loob;
        }

        // 生成单号  存入表中
        $tradnum = date("YmdHis") . mt_rand(100, 999) . mt_rand(100, 999);
        /// TODO 请求用户系统 获得用户对应的卡片
        $toCard = "7782890000154157658537443";
        $fromCard = "7782890000154157663863414";

        $sql = sprintf("INSERT INTO pay_flow (note,trade_type,notify_url,out_trade_no,ctime,total_fee,tocard,touid, tousercode, fromcard, fromuid, fromusercode ,body, tradnum, paystatus) VALUES ('%s','%s','%s','%s',%d,%d,'%s',%d,'%s','%s',%d,'%s','%s','%s',%d)"
            , $orderBodys["note"], $orderBodys["trade_type"], $orderBodys["notify_url"], $orderBodys["out_trade_no"], time(), $orderBodys["total_fee"], $toCard, $toUid, $toUsercode, $fromCard, $fromUid, $fromUsercode, $orderBodys["body"], $tradnum, 0);
        if ($this->db->query($sql)) {
            return json(["tradnum" => $tradnum]);
        }
        return error("失败");
    }

    /** 支付
     * @param $params
     */
    function pay($params)
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
        $db->free_result($que);
        if (empty($row)) {
            return error("无此单");
        }

        $paramsPOST = [];
        $paramsPOST["f"] = "trans";

        $p = [];
        $p["from"] = $row["fromcard"];
        $p["to"] = $row["tocard"];
        $p["psw"] = $params["pwd"];
        $p["money"] = $row["total_fee"];
        $p["desc"] = $row["note"];

        $paramsPOST["p"] = json_encode($p);

        $res1 = postCoinOS( $paramsPOST,false);


        $sql = null;
        if ($res1->st == 1) {
            /// 转账成功 更新状态
            $sql = sprintf("UPDATE  pay_flow SET paystatus=1  WHERE id=%d ", $params["id"]);
        } else {
            $sql = sprintf("UPDATE  pay_flow SET paystatus=2  WHERE id=%d ", $params["id"]);
        }
        if ($db->query($sql)) {
            $row["paystatus"] = $res1->st == 1 ? 1 : 2;
        }

        //  发送成交消息
        $net = new NetWork();
        $res = $net->post($row["notify_url"], $row);
        if ($res === 'success') {
            $sql = sprintf("UPDATE  pay_flow SET notify_status=1  WHERE id=%d ", $params["id"]);
            $db->query($sql);
        }

        if ($res1->st == 1) {
            return json($row);
        } else {
            return error($res1->msg);
        }

    }

    /** 分账（如：商家想退款）
     * @param $params
     */
    function fashionable($params)
    {
        $loob = Check::willPass($params, [
            "uid",//付款人  多人用英文逗号隔开
            "usercode",//付款人  多人用英文逗号隔开
            "body",//商品描述              多个用英文逗号隔开
            "out_trade_no",//商户订单号   多个用英文逗号隔开
            "total_fee",//总金额           多个用英文逗号隔开
            "trade_type",//交易类型        多个用英文逗号隔开
            "note",//备注                   多个用英文逗号隔开
            "notify_url",//通知地址
            "sign",//签名
            "nonce_str",//随机字符串
            "timestamp",//当前时间点（秒）
            "pwd",//转账密码
            "appid",//商户应用ID
        ]);

        if ($loob !== 1) {
            return $loob;
        }

        $fromUid = $params["uid"];
        $fromUsercode = $params["usercode"];
        //验 商家
        $row = $this->deMerchants($params);

        $certName = null;
        $toUid = null;
        $toUsercode = null;
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
        $loob = $this->deSign($params, $certName);
        if ($loob !== 1) {
            return $loob;
        }
        // 生成单号  存入表中
        $tradnum = date("YmdHis") . mt_rand(100, 999) . mt_rand(100, 999);
        /// TODO 请求用户系统 获得用户对应的卡片
        $toCard = "7782890000154157658537443";
        $fromCard = "7782890000154157663863414";

        $sql = sprintf("INSERT INTO pay_flow (note,trade_type,notify_url,out_trade_no,ctime,total_fee,tocard,touid, tousercode, fromcard, fromuid, fromusercode ,body, tradnum, paystatus) VALUES ('%s','%s','%s','%s',%d,%d,'%s',%d,'%s','%s',%d,'%s','%s','%s',%d)"
            , $params["note"], $params["trade_type"], $params["notify_url"], $params["out_trade_no"], time(), $params["total_fee"], $toCard, $toUid, $toUsercode, $fromCard, $fromUid, $fromUsercode, $params["body"], $tradnum, 0);
        if (!$this->db->query($sql)) {
            return error("失败");
        }
        $params["id"] = $this->db->insert_id();
        $paramsPOST = [];
        $paramsPOST["f"] = "trans";

        $p = [];
        $p["from"] = $fromCard;
        $p["to"] = $toCard;
        $p["psw"] = $params["pwd"];
        $p["money"] = $params["total_fee"];
        $p["desc"] = $params["note"];

        $paramsPOST["p"] = $p;

        $res1 = postCoinOS( $paramsPOST,false);


        $sql = null;
        if ($res1->st == 1) {
            /// 转账成功 更新状态
            $sql = sprintf("UPDATE  pay_flow SET paystatus=1  WHERE id=%d ", $params["id"]);
        } else {
            $sql = sprintf("UPDATE  pay_flow SET paystatus=2  WHERE id=%d ", $params["id"]);
        }
        if ($this->db->query($sql)) {
            $row["paystatus"] = $res1->st == 1 ? 1 : 2;
        }

        //  发送成交消息
        $net = new NetWork();
        $res = $net->post($params["notify_url"], $params);
        if ($res === 'success') {
            $sql = sprintf("UPDATE  pay_flow SET notify_status=1  WHERE id=%d ", $params["id"]);
            $this->db->query($sql);
        }

        if ($res1->st == 1) {
            return json($params);
        } else {
            return error($res1->msg);
        }

    }

    function fashionable_123($params)
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

        // 暂时 限制长度为1
        if (count($fromUids) > 1) {
            return error("uid 只能为一个", $fromUids);
        }
        if (count($fromUids) != count($fromUsercodes)) {
            return error("uid 与 usercode 数量不一致");
        }

        //验 商家
        $orderBodys = $this->deOrderBody($params["order_body"]);
        $row = $this->deMerchants($orderBodys);

        $certName = null;
        $toUid = null;
        $toUsercode = null;
        if (is_array($row)) {
            $certName = $row["cert"];
            $toUsercode = $row["usercode"];
            $toUid = $row["uid"];

            foreach ($fromUids as $fromUid) {
                if ($fromUid == $toUid) {
                    return error("不可以与自身交易");
                }
            }
            foreach ($fromUsercodes as $fromUsercode) {
                if ($fromUsercode == $toUsercode) {
                    return error("不可以与自身交易");
                }
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
        $bodys = explode(",", $orderBodys["body"]);
        $notes = explode(",", $orderBodys["note"]);
        $trade_types = explode(",", $orderBodys["trade_type"]);
        $out_trade_nos = explode(",", $orderBodys["out_trade_no"]);
        $total_fees = explode(",", $orderBodys["total_fee"]);
        if (count($bodys) != count($notes)
            && count($bodys) != count($trade_types)
            && count($bodys) != count($out_trade_nos)
            && count($bodys) != count($total_fees)) {

            return error("order_body 内参数 数量不一致");

        }
        $count = count($bodys);
        $sql = "";
        for ($num = 0; $num < $count; $num++) {
            $body = $bodys[$num];
            $note = $notes[$num];
            $trade_type = $trade_types[$num];
            $out_trade_no = $out_trade_nos[$num];
            $total_fee = $total_fees[$num];
            $tradnum = date("YmdHis") . mt_rand(100, 999) . mt_rand(100, 999);
            /// TODO 请求用户系统 获得用户对应的卡片
            $toCard = "9072000000153922506995820";
            $fromCard = "9072000000153922344546769";
            if ($count - 1 == $num) {
                $sql .= sprintf(" ('%s','%s','%s','%s',%d,%d,'%s',%d,'%s','%s',%d,'%s','%s','%s',%d)"
                    , $note, $trade_type, $orderBodys["notify_url"], $out_trade_no, time(), $total_fee, $toCard, $toUid, $toUsercode, $fromCard, $fromUid, $fromUsercode, $body, $tradnum, 0);

            } else {
                $sql .= sprintf(" ('%s','%s','%s','%s',%d,%d,'%s',%d,'%s','%s',%d,'%s','%s','%s',%d),"
                    , $note, $trade_type, $orderBodys["notify_url"], $out_trade_no, time(), $total_fee, $toCard, $toUid, $toUsercode, $fromCard, $fromUid, $fromUsercode, $body, $tradnum, 0);

            }

        }

        $sql = "INSERT INTO pay_flow (note,trade_type,notify_url,out_trade_no,ctime,total_fee,tocard,touid, tousercode, fromcard, fromuid, fromusercode ,body, tradnum, paystatus) VALUES  " . $sql;

        if ($this->db->query($sql)) {
            return json("成功");
        }


        return json("失败");
    }

    /** 账单查询
     * @param $params
     */
    function bill($params)
    {
        $loob = Check::willPass($params, ["appid"]);
        if ($loob !== 1) {
            return $loob;
        }

        $db = $this->db;
        $que = $db->query(sprintf("SELECT usercode FROM payplat_access WHERE appid='%s' LIMIT 1", $params["appid"]));
        $row = $db->fetch_assoc($que);


        if (empty($row)) {
            return error("无此商家");
        }
        $db->free_result($que);

        $sql = sprintf("SELECT * FROM pay_flow WHERE tousercode='%s' ", $row["usercode"]);
        $sql = $this->period($sql, $params);
        if (isResponse($sql)) {
            return $sql;
        }
        $que = $db->query($sql);

        $arr = [];
        while ($row = $db->fetch_assoc($que)) {
            $arr[] = $row;
        }
        $db->free_result($que);

        return json($arr);
    }
}