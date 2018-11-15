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
    function base64(){

        echo base64_encode("wzh");
        die(base64_decode(base64_encode("wzh")));
    }
    function en($params)
    {
        $signObj = new Signature();
        $url = $signObj->enSign($params, BASEPATH . "crt/coinapi.key");
//        $url = urlencode($url);

        echo $url;
        die(PHP_EOL);

    }
    function  de($params){
        $signObj = new Signature();
        $url = $signObj->deSign($params, BASEPATH . "crt/mxapi_2_pub.key");
//        $url = urlencode($url);

        echo $url;

        return json($params);
    }

    function add_money($params)
    {
        $loob = Check::willPass($params, ["acctid"]);
        if ($loob !== 1) {
            return $loob;
        }

        $p = [];
        $p["f"] = "increase";
        $p["p"] = ["acctCode" => $params["acctid"], "money" => "1844674407370955"];

        return postCoinOS($p);

    }

}