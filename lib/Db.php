<?php

class Db {
    var $querynum = 0;
    var $link;
    var $charset;


    public static function init(){
        $db = new Db();
        $params = include (BASEPATH."lib/database.php");
        $db->connect($params["hostname"],$params["username"],$params["password"],$params["database"]);
        $db->query("set names utf8;");

        return $db;
    }


    function connect($dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $halt = TRUE) {
        if($pconnect) {
            if(!$this->link = @mysql_pconnect($dbhost, $dbuser, $dbpw)) {
                die($this->error());
            }
        } else {
            if(!$this->link = @mysql_connect($dbhost, $dbuser, $dbpw, 1)) {
                die($this->error());
            }
        }

        if($this->version() > '4.1') {
            if($this->charset) {
                @mysql_query("SET character_set_connection=$this->charset, character_set_results=$this->charset, character_set_client=binary", $this->link);
            }
            if($this->version() > '5.0.1') {
                @mysql_query("SET sql_mode=''", $this->link);
            }
        }
        if($dbname) {
            @mysql_select_db($dbname, $this->link);
        }

    }

    function select_db($dbname) {
        return mysql_select_db($dbname, $this->link);
    }

    function fetch_array($query, $result_type = MYSQL_ASSOC) {
        return mysql_fetch_array($query, $result_type);
    }

    function query($sql, $type = '',$callbacks='') {
        $func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';

        if(!($query = $func($sql, $this->link)) && $type != 'SILENT') {
            if ($callbacks == ''){
                die($this->error());
            }else{
                return false;
            }

        }
        $this->querynum++;
        return $query;
    }
    function fetch_assoc ($query) {
        return mysql_fetch_assoc($query);
    }
    function autocommit(){

        mysql_query("START TRANSACTION",$this->link);
        //        START TRANSACTION

    }
    function commit() {
        mysql_query("COMMIT",$this->link);

    }
    function rollback(){
        mysql_query("ROLLBACK",$this->link);

    }
    function affected_rows() {
        return mysql_affected_rows($this->link);
    }

    function error() {
        return "MySQL Error : ".(($this->link) ? mysql_error($this->link) : mysql_error());
    }

    function errno() {
        return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
    }

    function result($query, $row) {
        $query = @mysql_result($query, $row);
        return $query;
    }

    function num_rows($query) {
        $query = mysql_num_rows($query);
        return $query;
    }

    function num_fields($query) {
        return mysql_num_fields($query);
    }

    function free_result($query) {
        return mysql_free_result($query);
    }

    function insert_id() {
        return ($id = mysql_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
    }

    function fetch_row($query) {
        $query = mysql_fetch_row($query);
        return $query;
    }

    function fetch_fields($query) {
        return mysql_fetch_field($query);
    }

    function version() {
        return mysql_get_server_info($this->link);
    }

    function close() {
        return mysql_close($this->link);
    }

    function get_one($sql, $type = '', $expires = 3600, $dbname = '')
    {
        $query = $this->query($sql, $type, $expires, $dbname);
        $row = $this->fetch_array($query);
        return $row;
    }

    function get_row($sql, $type = '', $expires = 3600, $dbname = '') {
        $query = $this->query($sql, $type, $expires, $dbname);
        $row = $this->fetch_array($query);
        return $row;
    }

    function get_all($sql, $type = '', $expires = 3600, $dbname = '') {
        $query = $this->query($sql, $type, $expires, $dbname);
        $return = array();
        while($row = $this->fetch_array($query)) {
            $return[] = $row;
        }
        return $return;
    }

    function select($sql, $keyfield = '', $keyfieldtype=true)
    {
        $array = array();
        $result = $this->query($sql);
        while($r = $this->fetch_array($result))
        {
            if($keyfield)
            {
                if ($keyfieldtype) {
                    $key = $r[$keyfield];
                    $array[$key] = $r;
                } else {
                    $array[] = $r[$keyfield];
                }
            }
            else
            {
                $array[] = $r;
            }
        }
        $this->free_result($result);
        return $array;
    }


}

?>