<?php

/**
 * Created by PhpStorm.
 * User: 吴宏佳
 * Date: 2018/11/28
 * Time: 下午4:49
 */

require_once BASEPATH . "lib/Db.php";
require_once BASEPATH . "lib/NetWork.php";

class Synchronous
{

    private $db;

    /**
     * @return Db
     */
    public function getDb()
    {
        if ($this->db == null){
            $this->db = Db::init();
        }
        return $this->db;
    }

    function __destruct()
    {
        if ($this->getDb()){
            $this->getDb()->close();
        }
    }


    function __construct()
    {

        $this->getDb()->query("CREATE TABLE IF NOT EXISTS `coin_flow_user_mapping` (
 `id` INT NOT NULL AUTO_INCREMENT , 
 `usercode` TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户code' , 
 `xzid` TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '勋章 在积分系统账户id' , 
 `mryid` TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '荣誉点 在积分系统账户id' , 
 `username` TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户名' , 
 PRIMARY KEY (`id`)) ENGINE = InnoDB;");
    }

    /** usercode
     * @param $params
     * @return Response
     */
    function info($params)
    {
        $usercode = $params["usercode"];
        $username = null;

        $p["p"] = json_encode($params);
        $p["f"] = "Api_getUinfoByCode";
        $res = NetWork::get(MUXIN_URL, $p);

        if (!empty($res->info->uinfo->name)) {
            $username = $res->info->uinfo->name;

        } else {
            return error("用户数据同步失败");
        }

        $params["username"] = $username;
        $id = $this->insertInto($params);

        return json(["id" => $id]);
    }

    /** searchkey
     * @param $params
     */
    function collection($params){

        $params["companycode"] = 123456;
        $p["p"] = json_encode($params);
        $p["f"] = "Api_searchUinfo";
        $res = NetWork::get(MUXIN_URL, $p);
        if (!empty($res->info->uinfo)) {
            foreach ($res->info->uinfo as $obj){
                $params["username"] = $obj->name;
                $params["usercode"] = $obj->usercode;
                $id = $this->insertInto($params);
            }
            return json($res->info->uinfo);

        } else {
            return json([]);
        }


    }

    private  function insertInto($params){
        $usercode = $params["usercode"];
        $username = $params["username"];
        $que = $this->getDb()->query("SELECT id FROM coin_flow_user_mapping WHERE usercode='{$usercode}' LIMIT 1");
        $id = null;
        if ($row = $this->getDb()->fetch_assoc($que)) {
            $id = $row["id"];
            $this->getDb()->free_result($que);

            $this->getDb()->query("UPDATE coin_flow_user_mapping SET username = '{$username}' WHERE id={$id} ");


        } else {

            $this->getDb()->query("INSERT INTO coin_flow_user_mapping (usercode,username) VALUES ('{$usercode}','{$username}') ");
            $id = $this->getDb()->insert_id();
        }

        return $id;
    }


}