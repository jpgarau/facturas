<?php

namespace modelo\Cliente;

use modelo\Conexion\Conexion;
use mysqli_driver;

require_once 'Conexion.php';

class Cliente
{
    private $id;
    private $num_doc;
    private $correo;

    public function __construct()
    {
        $driver = new mysqli_driver();
        $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;
        $this->id = 0;
        $this->num_doc = 0;
        $this->correo = "";
    }

    //getters y setters
    public function __get($name)
    {
        return $this->{$name};
    }

    public function __set($name, $value)
    {
        if($name==='num_doc'){
            $value = filter_var($value, FILTER_VALIDATE_INT);
        }
        $this->{$name} = $value;
    }

    public function agregar()
    {
        $arr = array('exito' => false, 'msg' => 'Error al guardar');
        try {
            $num_doc = $this->__get('num_doc');
            $correo = $this->__get('correo');
            if (!(is_bool($num_doc) || is_bool($correo))) {
                $sql = 'INSERT INTO clientes (num_doc, correo) VALUES (?,?)';
                $mysqli = Conexion::abrir();
                $stmt = $mysqli->prepare($sql);
                if ($stmt !== FALSE) {
                    $stmt->bind_param('is', $num_doc, $correo);
                    $stmt->execute();
                    $stmt->close();
                    $mysqli->close();
                    $arr = array('exito' => true, 'msg' => '');
                }else{
                    $arr['msg'] = "SQL";
                }
            }else{
                $arr['msg'] = "Formato incorrecto";
                $arr[] = array('num_doc'=>$num_doc, 'correo'=>$correo);
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }

    public function buscarCliente()
    {
        $arr = array('exito' => false, 'msg' => 'Error al buscar');
        try {
            $num_doc = $this->__get('num_doc');
            if(!is_bool($num_doc)){
                $sql = 'SELECT num_doc, correo FROM clientes WHERE num_doc=? LIMIT 1';
                $mysqli = Conexion::abrir();
                $stmt = $mysqli->prepare($sql);
                if($stmt!==FALSE){
                    $stmt->bind_param('i', $num_doc);
                    $stmt->execute();
                    $rs = $stmt->get_result();
                    $encontrado = $rs->num_rows;
                    $stmt->close();
                    $mysqli->close();
                    if($encontrado > 0){
                        $cliente = $rs->fetch_assoc();
                        $arr = array('exito'=>true, 'msg' => '', 'encontrado'=>$encontrado, $cliente);
                    }else{
                        $arr = array('exito'=>false, 'msg' => "No encontrado",'encontrado'=>$encontrado);
                    }
                }
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }
}
