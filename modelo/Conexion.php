<?php 

namespace modelo\Conexion;

use mysqli;

class Conexion{
    static $mysqli;
    public static function abrir(){
        $host   = 'localhost';
        $dbname = 'facturas_web';
        $user   = 'root';
        $pass   = '';
        $port   = '3306';
        
        $mysqli = new mysqli($host, $user, $pass, $dbname, $port);
        $mysqli->set_charset('utf8');

        return $mysqli;
    }
}