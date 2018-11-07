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
    function en($params)
    {
        $signObj = new Signature();
        $url = $signObj->enSign($params, BASEPATH . "crt/mxapi.key");
//        $url = urlencode($url);

        echo $url;
        return json(1);

    }
    function  de($params){
        $signObj = new Signature();
        $url = $signObj->deSign($params, BASEPATH . "crt/mxapi.crt");
//        $url = urlencode($url);

        echo $url;

        return json($params);
    }

}