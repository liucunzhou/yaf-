<?php
class DataBase{

    public static function getInstance(string $db_server='master'){
        $config = Yaf_Registry::get("config");
    
        $db_config = $config->db->$db_server;

        try{
            $db = new PDO('mysql:host='.$db_config->host.';dbname='.$db_config->database, $db_config->username, $db_config->password,[PDO::ATTR_TIMEOUT=>1]);
            $db->exec("SET NAMES utf8");
            $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        return $db;
    }
}