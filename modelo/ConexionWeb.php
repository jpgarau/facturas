<?php 

namespace modelo\ConexionWeb;

use mysqli;

class ConexionWeb{
    static $mysqli;
    public static function abrir(){
        $host   = "192.168.1.131";
        $dbname = 'mgcagontech';
        $user   = 'webuser';
        $pass   = 'W38-Us3r*AGON';
        $port   = '3366';
        
        $mysqli = new mysqli($host, $user, $pass, $dbname, $port);
        $mysqli->set_charset('utf8');

        return $mysqli;
    }
}