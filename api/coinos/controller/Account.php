<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/6
 * Time: 17:00
 */
class Account
{

    /** 创建帐户
     * @param $params
     * @return int|Response
     */
    function create($params)
    {
        $loob = Check::willPass($params, ["usercode"]);
        if ($loob !== 1) {
            return $loob;
        }

        $p = [];
        $p["f"] = "regist";
        $p["p"] = ["coinCode" => COIN_CODE, "bindId" => $params["usercode"]];
        //可选
        $p["p"] = array_merge($p["p"],Check::optional($params,["pwd"=>"psw"]));
        return postCoinOS($p);

    }

    /** 查询余额
     * @param $params
     * @return int|Response
     */
    function balance($params)
    {
        $loob = Check::willPass($params, ["acctid"]);
        if ($loob !== 1) {
            return $loob;
        }

        $p = [];
        $p["f"] = "queryAcct";
        $p["p"] = ["acctCode" => $params["acctid"]];

        return postCoinOS($p);

    }

    /** 查询交易记录
     * @param $params
     * @return int|Response
     */
    function records($params)
    {
        $loob = Check::willPass($params, ["acctid"]);
        if ($loob !== 1) {
            return $loob;
        }

        $p = [];
        $p["f"] = "queryTrans";
        $p["p"] = ["acctCode" => $params["acctid"]];
        //可选
        $p["p"] = array_merge($p["p"],Check::optional($params,["page","count"=>"pageSize"]));

        return postCoinOS($p);

    }

    /** 冻结
     * @param $params
     * @return int|Response
     */
    function freeze($params)
    {
        $loob = Check::willPass($params, ["acctid","money"]);
        if ($loob !== 1) {
            return $loob;
        }

        $p = [];
        $p["f"] = "freeze";
        $p["p"] = ["acctCode" => $params["acctid"],"money"=>$params["money"]];
        //可选
        $p["p"] = array_merge($p["p"],Check::optional($params,["desc"]));

        return postCoinOS($p);
    }

    /** 解冻
     * @param $params
     * @return int|Response
     */
    function thaw($params)
    {
        $loob = Check::willPass($params, ["acctid","money"]);
        if ($loob !== 1) {
            return $loob;
        }

        $p = [];
        $p["f"] = "unfreeze";
        $p["p"] = ["acctCode" => $params["acctid"],"money"=>$params["money"]];
        //可选
        $p["p"] = array_merge($p["p"],Check::optional($params,["desc"]));


        return postCoinOS($p);
    }

    /** 更新密码
     * @param $params
     * @return int|Response
     */
    function up_pwd($params){
        $loob = Check::willPass($params, ["acctid","pwd"]);
        if ($loob !== 1) {
            return $loob;
        }

        $p = [];
        $p["f"] = "updatepsw";
        $p["p"] = ["acctCode" => $params["acctid"],"psw"=>$params["pwd"]];


        return postCoinOS($p);
    }


}