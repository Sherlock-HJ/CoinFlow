<?php

/**
 * Created by PhpStorm.
 * User: 吴宏佳
 * Date: 2018/11/27
 * Time: 上午8:33
 */
require_once BASEPATH . "lib/Db.php";

class Account
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

    function __construct()
    {

    }
    function __destruct()
    {
        if ($this->getDb()){
            $this->getDb()->close();
        }    }


    /** 查询余额
     * @param $params
     * @return int|Response
     */
    function balance($params)
    {
        $loob = Check::willPass($params, ["usercode"]);
        if ($loob !== 1) {
            return $loob;
        }
        $acctid = $this->utoa($params["usercode"]);

        if ($acctid == null) {

            return error("勋章账户创建失败");
        }

        $p = [];
        $p["f"] = "queryAcct";
        $p["p"] = ["acctCode" => $acctid];

        $res = postCoinOS($p, false);
        if ($res) {
            if ($res->st === 1) {
                $amount = intval($res->result->acctMoney);
                return json(["level" => $this->dismantling($amount), "amount" => $this->narrow($amount)]);
            } else {
                return error($res->msg);
            }
        } else {
            return error(["info" => "CoinOS返回错误", "error" => [$params, $res]]);
        }


    }


    /** 查询发放勋章记录
     * @param $params
     * @return int|Response
     */
    function warning_records($params)
    {
        $page = null;
        $count = null;
        if (!empty($params['page'])) {
            $page = $params['page'];
        }
        if (!empty($params['pageSize'])) {
            $count = $params['pageSize'];
        }
        return $this->queryTrans(COIN_ADMIN_ACCTID_SJ, $page, $count);

    }

    /** 查询颁发勋章记录
     * @param $params
     * @return int|Response
     */
    function medal_records($params)
    {
        $page = null;
        $count = null;
        if (!empty($params['page'])) {
            $page = $params['page'];
        }
        if (!empty($params['pageSize'])) {
            $count = $params['pageSize'];
        }
        return $this->queryTrans(COIN_ADMIN_ACCTID_XZ, $page, $count);

    }

    /** 查询交易记录
     * @param $params
     * @return int|Response
     */
    function records($params)
    {
        $page = null;
        $count = null;
        $loob = Check::willPass($params, ["usercode"]);
        if ($loob !== 1) {
            return $loob;
        }

        $acctid = $this->utoa($params["usercode"]);

        if ($acctid == null) {

            return error("勋章账户创建失败");
        }

        if (!empty($params['page'])) {
            $page = $params['page'];
        }
        if (!empty($params['pageSize'])) {
            $count = $params['pageSize'];
        }
        return $this->queryTrans($acctid, $page, $count);
    }

    /** 查询交易记录
     * @param $params
     * @return int|Response
     */
    private function queryTrans($acctid, $page = 1, $count = 10)
    {
        $p = [];
        $p["f"] = "queryTrans";
        $p["p"] = ["acctCode" => $acctid, "page" => $page, "pageSize" => $count];

        $res = postCoinOS($p, false);

        if ($res && $res->st === 1 && property_exists($res, 'result')) {

            $rule = include BASEPATH . 'api/medal/model/rule.php';

            $arr = array();
            foreach ($res->result as $item) {



                if (COIN_ADMIN_ACCTID_XZ == $item->toAcctCode || COIN_ADMIN_ACCTID_SJ == $item->toAcctCode) {
                    $item->toName = COIN_PLATFORM;
                } else {
                    $toAcctCode = $item->toAcctCode;
                    $que = $this->getDb()->query("SELECT username FROM coin_flow_user_mapping  WHERE xzid='{$toAcctCode}' LIMIT 1");
                    $row = $this->getDb()->fetch_assoc($que);
                    $this->getDb()->free_result($que);
                    if (!empty($row["username"])) {
                        $item->toName = $row["username"];
                    }
                }

                if (COIN_ADMIN_ACCTID_XZ == $item->fromAcctCode || COIN_ADMIN_ACCTID_SJ == $item->fromAcctCode) {
                    $item->fromName = COIN_PLATFORM;
                } else {
                    $fromAcctCode = $item->fromAcctCode;
                    $que = $this->getDb()->query("SELECT username FROM coin_flow_user_mapping  WHERE xzid='{$fromAcctCode}' LIMIT 1");
                    $row = $this->getDb()->fetch_assoc($que);
                    $this->getDb()->free_result($que);
                    if (!empty($row["username"])) {
                        $item->fromName = $row["username"];
                    }
                }

                if ($item->transMoney <0){
                    $xz = $rule['sj'];

                }else{
                    $xz = $rule['xz'];

                }
                $name = $xz[count($xz)-1]['name'];
                $xs = $xz[count($xz)-1]['xs'];
                $item->num = $item->transMoney /($xs*1000);
                $item->name = $name;

                $item->transMoney /= 1000;

                array_push($arr, $item);
            }
            return json($arr);

        } else {
            return error($res->msg);
        }


    }


    /** 更新密码
     * @param $params
     * @return int|Response
     */
    function up_pwd($params)
    {
        $loob = Check::willPass($params, ["acctid", "pwd"]);
        if ($loob !== 1) {
            return $loob;
        }

        $p = [];
        $p["f"] = "updatepsw";
        $p["p"] = ["acctCode" => $params["acctid"], "psw" => $params["pwd"]];


        return postCoinOS($p);
    }

    /** 发放勋章 usercode amount
     * @param $params
     * @return int|Response
     */
    function issue_medal($params)
    {
        $loob = Check::willPass($params, ["usercode", "amount", "reason"]);
        if ($loob !== 1) {
            return $loob;
        }
        $usercodes = explode(",", $params["usercode"]);
        $amounts = explode(",", $params["amount"]);
        $reasons = explode(",", $params["reason"]);
        $user_count = count($usercodes);
        $amount_count = count($amounts);
        $reason_count = count($reasons);

        if ($amount_count != $user_count && $amount_count != $reason_count) {
            return error("用户数、颁发数、颁发原因数要相等");
        }
        $acctids = array();
        for ($num = 0; $num < $user_count; $num++) {
            $amount = $amounts[$num];
            $usercode = $usercodes[$num];
            $reason = $reasons[$num];

            if (empty($usercode)){
                return error("请填写用户");
            }

            if (empty($amount)){
                return error("请填写数量");
            }

            if (empty($reason)){
                return error("请填写原因");
            }


            $amount = $this->amplification($amount);
            if ($amount < 10) {
                return error("勋章余量小于0.01,无法发放");
            }

            if ($amount % 10 > 0) {
                return error("勋章发放数须是0.01的整数倍");
            }

            $acctid = $this->utoa($usercode);

            if ($acctid == null) {

                return error("勋章账户创建失败");
            }

            array_push($acctids, $acctid);

        }

        $error = 0;

        for ($num = 0; $num < $user_count; $num++) {
            $amount = $amounts[$num];
            $acctid = $acctids[$num];
            $reason = $reasons[$num];


            $paramsPOST = [];
            $paramsPOST["f"] = "trans";

            $p = [];
            $p["from"] = COIN_ADMIN_ACCTID_XZ;
            $p["to"] = $acctid;
            $p["psw"] = COIN_PWD_XZ;
            $p["money"] = $this->amplification($amount);
            $p["desc"] = "颁发勋章-原因：" . $reason;

            $paramsPOST["p"] = $p;

            $res = postCoinOS($paramsPOST, false);
            if ($res && $res->st === 1 && !empty($res->result->transID)) {

            } else {
                $error++;
            }
        }

        return json(["success" => count($usercodes) - $error, "error" => $error, "info" => $res->msg]);

    }

    /**
     * 发放警示 usercode amount
     */
    function issue_warning($params)
    {
        $loob = Check::willPass($params, ["usercode", "amount", "reason"]);
        if ($loob !== 1) {
            return $loob;
        }
        $usercodes = explode(",", $params["usercode"]);
        $amounts = explode(",", $params["amount"]);
        $reasons = explode(",", $params["reason"]);
        $user_count = count($usercodes);
        $amount_count = count($amounts);
        $reason_count = count($reasons);

        if ($amount_count != $user_count && $amount_count != $reason_count) {
            return error("用户数、发放数、发放原因数要相等");
        }


        $acctids = array();
        for ($num = 0; $num < $user_count; $num++) {
            $amount = $amounts[$num];
            $usercode = $usercodes[$num];
            $reason = $reasons[$num];

            if (empty($usercode)){
                return error("请填写用户");
            }

            if (empty($amount)){
                return error("请填写数量");
            }

            if (empty($reason)){
                return error("请填写原因");
            }

            if ($amount > -1) {
                return error("警示余量中有大于-1的值,无法发放");
            }
            $amount = $this->amplification($amount);

            if ($amount % 10 < 0) {
                return error("警示发放数须是-1的整数倍");
            }

            $acctid = $this->utoa($usercode);

            if ($acctid == null) {

                return error("勋章账户创建失败");
            }
            array_push($acctids, $acctid);

        }

        $error = 0;

        for ($num = 0; $num < $user_count; $num++) {
            $amount = $amounts[$num];
            $acctid = $acctids[$num];
            $reason = $reasons[$num];


            $p["acctid"] = $acctid;
            $p["amount"] = $this->amplification($amount);
            $p["from"] = COIN_ADMIN_ACCTID_SJ;
            $p["note"] = "发放警示-原因：" . $reason;
            $res = $this->increase($p);
            if ($res && $res->st === 1 && !empty($res->result->transID)) {

                $cons = $this->get_sj_toed();
                $cons += intval($p["amount"]);
                $this->set_sj_toed($cons);



            } else {
                $error++;
            }
        }

        return json(["success" => count($usercodes) - $error, "error" => $error, "info" => $res->msg]);


    }

    private function set_sj_toed($cons){
        $path = RUNTIME_PATH.'sj.im';
        file_put_contents($path,$cons);

    }
    private function get_sj_toed(){
        $path = RUNTIME_PATH.'sj.im';

        $cons = 0;
        if (file_exists($path)){
            $cons = intval(file_get_contents($path));
        }
        return $cons;
    }
    /** 增加 账户的金额
     * @param $params
     * @return int|Response
     */
    private function increase($params)
    {
        $loob = Check::willPass($params, ["acctid", "amount", "from", "note"]);
        if ($loob !== 1) {
            return $loob;
        }
        $p = [];
        $p["f"] = "increase";
        $p["p"] = ["acctCode" => $params["acctid"], "money" => $params["amount"], "fromAcct" => $params["from"], "desc" => $params["note"]];

        return postCoinOS($p, false);
    }

    function transfer($params)
    {
        $loob = Check::willPass($params, ["usercode", "tousercode", "amount", "note"]);
        if ($loob !== 1) {
            return $loob;
        }

        $acctid = $this->utoa($params["usercode"]);

        if ($acctid == null) {

            return error("勋章账户创建失败");
        }

        $toAcctid = $this->utoa($params["tousercode"]);

        if ($toAcctid == null) {

            return error("勋章账户创建失败");
        }


        $amount = $this->amplification($params["amount"]);
        if ($amount < 10) {
            return error("勋章余量小于0.01,无法赠予");
        }

        if ($amount % 10 > 0) {
            return error("勋章赠予数须是0.01的整数倍");
        }

        $paramsPOST = [];
        $paramsPOST["f"] = "trans";

        $p = [];
        $p["from"] = $acctid;
        $p["to"] = $toAcctid;
        $p["psw"] = COIN_PWD_XZ;
        $p["money"] = $amount * (1 - COIN_TRANSFER_WEAR);
        $p["desc"] = "赠予勋章-原因：" . $params["note"];

        $paramsPOST["p"] = $p;

        $res1 = postCoinOS($paramsPOST, false);

        $paramsPOST = [];
        $paramsPOST["f"] = "trans";

        $p = [];
        $p["from"] = $acctid;
        $p["to"] = COIN_ADMIN_ACCTID_MS;
        $p["psw"] = COIN_PWD_XZ;
        $p["money"] = $amount * COIN_TRANSFER_WEAR;
        $p["desc"] = "磨损";

        $paramsPOST["p"] = $p;

        $res2 = postCoinOS($paramsPOST, false);

        if (!empty($res1) && !empty($res2) && !empty($res1->result->transID) && !empty($res2->result->transID)) {
            return json("赠予成功");

        }
        return error("赠予失败-" . $res1 - msg . '-' . $res2->msg);

    }

//    手动调用
    function increase_admin($params)
    {
        $amount =  $this->xz_total()*1000;
        $p = [];
        $p["f"] = "increase";
        $p["p"] = ["acctCode" => COIN_ADMIN_ACCTID_XZ, "money" => $amount, "fromAcct" => "IN_XZ_123456", "desc" => "给勋章管理员账户充值"];


        return postCoinOS($p);
    }

    function balance_admin()
    {

        $p = [];
        $p["f"] = "queryAcct";
        $p["p"] = ["acctCode" => COIN_ADMIN_ACCTID_XZ];

        $res = postCoinOS($p, false);
        if ($res) {
            if ($res->st === 1) {
                $amount = intval($res->result->acctMoney);
                return json(["level" => $this->dismantling($amount), "amount" => $this->narrow($amount)]);
            } else {
                return error($res->msg);
            }
        } else {
            return error(["info" => "CoinOS返回错误", "error" => $res]);
        }
    }

    function sj_balance_admin()
    {

        $amount = $this->sj_total()*1000- $this->get_sj_toed();
        return json(["level" => $this->dismantling($amount), "amount" => $this->narrow($amount)]);

    }

    function rule()
    {
        return json(include BASEPATH . 'api/medal/model/rule.php');
    }

    function xz_rule()
    {
        $rule = include BASEPATH . 'api/medal/model/rule.php';
        $xzs = $rule['xz'];
        $sjs = $rule['sj'];
        $xml = include BASEPATH . 'html/xz_rule.php';

        return xml($xml);
    }

    function xs_allowance()
    {

        return json(['xz' => $this->xz_total(), 'sj' => $this->sj_total()]);
    }

    function  create($params){
        $loob = Check::willPass($params, ["usercode"]);
        if ($loob !== 1) {
            return $loob;
        }

        $p = [];
        $p["f"] = "regist";
        $p["p"] = ["coinCode" => COIN_CODE, "bindId" => $params['usercode'], "psw" => COIN_PWD_XZ];
        $res = postCoinOS($p, false);
        if ($res && $res->result && $res->result->acctID) {
            return json($res->result);

        }            return error(['msg'=>$res,'params'=>$p]);


    }
    /** 获取usercode 对应的 acctid
     * @param $params
     * @return int|Response
     */
    private function utoa($usercode)
    {

        $que = $this->getDb()->query("SELECT xzid FROM coin_flow_user_mapping WHERE usercode='{$usercode}' LIMIT 1");

        $acctid = null;
        $row = $this->getDb()->fetch_assoc($que);
        if (!empty($row["xzid"])) {
            $acctid = $row["xzid"];
            $this->getDb()->free_result($que);

        } else {
            $p = [];
            $p["f"] = "regist";
            $p["p"] = ["coinCode" => COIN_CODE, "bindId" => $usercode, "psw" => COIN_PWD_XZ];
            $res = postCoinOS($p, false);
            if ($res && $res->result && $res->result->acctID) {
                $acctid = $res->result->acctID;

                $this->getDb()->query("UPDATE coin_flow_user_mapping SET xzid='{$acctid}' WHERE usercode = '{$usercode}' ");

            } else {
                $acctid = null;
            }

        }


        return $acctid;
    }

    /**
     * @param int $amount 放大 用户发来 的数
     * @return float
     */
    private function amplification($amount)
    {

        return floor(floatval($amount) * 1000);
    }

    /**
     * @param int $amount 缩小 从积分系统获得 的数
     * @return float|int
     */
    private function narrow($amount)
    {
        return intval($amount) / 1000;

    }

    /**
     * @param int $amount 将 从积分系统获得 的数 按勋章等级规则 拆解
     * @return array
     */
    private function dismantling($amount)
    {
        $rules = include BASEPATH . 'api/medal/model/rule.php';
        if ($amount > 0) {
            $rules = $rules["xz"];

        } else {
            $rules = $rules["sj"];

        }
        $rem = $amount;

        $arr = [];

        foreach ($rules as $rule) {
            $total = $rule['total'];
            $xs = $rule["xs"] * 1000;

            //获得包含的个数 $num
            $num = floor($rem / $xs);
            //$num 不能 大于 $rule 中的总数$total
            $allowance = $num >= $total ? $total : $num;
            //保存最终的$num
            $r["num"] = $allowance;
            $r = array_merge($r, $rule);
            $arr[] = $r;

            //更新剩余的值
            $rem = $rem - $allowance * $xs;

        }
        return $arr;

    }

    /**
     * //勋章总共发行的数量
     */
    private function xz_total()
    {
        $rules = include BASEPATH . 'api/medal/model/rule.php';
        $rules = $rules["xz"];

        $xz_total = 0;
        foreach ($rules as $rule) {
            $xs = $rule['xs'];
            $total = $rule['total'];
            $xz_total += $xs * $total;

        }

        return $xz_total;
    }

    /**
     * //警示发行的数量
     */
    private function sj_total()
    {
        $rules = include BASEPATH . 'api/medal/model/rule.php';

        $rules = $rules["sj"];

        $sj_total = 0;
        foreach ($rules as $rule) {
            $xs = $rule['xs'];
            $total = $rule['total'];
            $sj_total += $xs * $total;

        }

        return $sj_total;

    }

}