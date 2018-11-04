<?php
/**
 * Created by PhpStorm.
 * User: wuhongjia
 * Date: 04/11/2018
 * Time: 21:56
 */

class Log
{

    private $fileDir;
    function __construct()
    {

        $fileDir = "runtime/log";
        if (!file_exists($fileDir)) {
            mkdir($fileDir);
        }
        $this->fileDir = $fileDir;
    }


    function toString($gen) {

        if (is_string($gen) || is_int($gen) || is_double($gen)) {
            return $gen . "";
        } else if (is_array($gen) || gettype($gen) == "object") {
            return json_encode($gen);
        } else {
            return "";
        }
    }

    function createlog($gen)
    {

        $info = $this->toString($gen["info"]);

        $time = $gen["time"];
        $ip = $gen["ip"];


        $html = "<tr><td>{$ip}</td><td>{$time}</td><td>{$info}</td></tr>";

        $tableEnd = "</table>";

        $filename = date("Y-m-d") . ".html";

        $filepath = $this->fileDir . "/" . $filename;

        if (file_exists($filepath)) {

            $file = fopen($filepath, "r+");
            if (fseek($file, -strlen($tableEnd), SEEK_END) == 0) {
                fwrite($file, $html.$tableEnd);
            }
            fclose($file);


        } else {

            $tableStart = "<meta charset='utf-8'><table border='1' cellspacing='0'><tr><th>IP</th><th>time</th><th>info</th></tr>";
            file_put_contents($filepath, $tableStart . $html . $tableEnd, FILE_APPEND);


        }

    }
    public static function clog($info){
        $log = new Log();

        $info = $log->toString($info);
        date_default_timezone_set("PRC");

        $arr["time"] = date("H:i:s");
        $arr["ip"] =  $_SERVER["REMOTE_ADDR"];
//    $arr["info"] =  urlencode($info);
        $arr["info"] =  $info;

        $log->createlog($arr);

    }
}