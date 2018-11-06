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

    function balance ($params){
        $loob = Check::willPass($params, ["usercode"]);
        if ($loob !== 1) {
            return $loob;
        }

        $params = [];
        $params["orgId"] = "153922337400001";
        $params["f"] = "queryAllAccount";
        $params["p"] = ["usercode"=>$params["usercode"]];

        $net = new NetWork();
        $res = $net->post(BoinOSBaseURL(),$params);

        return json($res);
    }
}