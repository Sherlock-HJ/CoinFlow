<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/6
 * Time: 17:00
 */
require_once BASEPATH . "lib/NetWork.php";

class Account
{

    function create($params){

    }
    function balance ($params){
        $loob = Check::willPass($params, ["usercode"]);
        if ($loob !== 1) {
            return $loob;
        }

        $params = [];
        $params["orgId"] = "153922337400001";
        $params["f"] = "queryAcct";
        $params["p"] = ["acctCode"=>$params["usercode"]];

        $net = new NetWork();
        $res = $net->post(BoinOSBaseURL(),$params);

        return json($res);
    }

//    //查询交易记录
//http://192.168.113.107:8085/api?orgId=153922337400001&f=queryTrans&p={"acctCode":"9072000000153922506995820","page":"1","pageSize":"5"}
//
////创建帐户
//http://192.168.113.107:8085/api?orgId=153922337400001&f=regist&p={"coinCode":"MRY","bindId":"1","psw":""}
}