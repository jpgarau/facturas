<?php

namespace modelo\Requerimiento;

use modelo\Conexion\Conexion;
use modelo\ConexionWeb\ConexionWeb;
use mysqli_driver;

require_once 'Conexion.php';
require_once 'ConexionWeb.php';

class Requerimiento
{
    private $id_requerimiento;
    private $Idorden;
    private $fecha;
    private $nro_doc;
    private $idcliente;
    private $requerimiento;
    private $fechaprometido;
    private $prioridad;
    private $estado;

    public function __construct()
    {
        $driver = new mysqli_driver();
        $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;
        $this->id_requerimiento = 0;
        $this->Idorden = 0;
        $this->fecha = "";
        $this->nro_doc = 0;
        $this->idcliente = 0;
        $this->requerimiento = "";
        $this->fechaprometido = "";
        $this->prioridad = 0;
        $this->estado = 0; // 1-pendiente, 2-en curso, 3-terminado, 4-retirado, 5-implementado
    }

    public function __get($name)
    {
        return $this->{$name};
    }

    public function __set($name, $value)
    {
        if ($name === 'Idorden' || $name === 'nro_doc' || $name === 'idcliente' || $name === 'prioridad' || $name === 'estado') {
            $value = filter_var($value, FILTER_VALIDATE_INT);
            if ($value === FALSE) $value = 0;
        }
        if ($name === 'fecha' || $name === 'requerimiento' || $name === 'fechaprometido') {
            $value = trim($value);
            $value = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if ($value === FALSE || is_null($value) || strlen($value) === 0) $value = true;
        }
        $this->{$name} = $value;
    }

    public function listarPendientes()
    {
        $arr = array('exito' => false, 'msg' => 'Error al listar pendientes');
        try {
            $pendientes = array();
            $pendientes2 = array();
            $pendientes3 = array();
            $nro_doc = $this->__get('nro_doc');
            $sql = 'SELECT * FROM requerimientos WHERE nro_doc = ?'; 
            $mysqli = Conexion::abrir();
            $stmt = $mysqli->prepare($sql);
            if($stmt !==false){
                $stmt->bind_param('i', $nro_doc);
                $stmt->execute();
                $res = $stmt->get_result();
                $encontrados = $res->num_rows;
                if($encontrados > 0){
                    $pendientes = $res->fetch_all(MYSQLI_ASSOC);
                }
                $sql2 = 'SELECT ordenes.Idorden, ordenes.estado, ordenes.idcliente, clientes.razonsocial, ordenes.fecha, ordenes.requerimiento, ordenes.fechaprometido FROM ordenes JOIN clientes ON ordenes.idcliente = clientes.idcliente WHERE ordenes.idcomponente=20 AND (ordenes.estado=1 OR ordenes.estado=0 OR ordenes.estado=9) AND replace(clientes.cuit,"-","") = ?';
                $mysqli2 = ConexionWeb::abrir();
                $stmt2 = $mysqli2->prepare($sql2);
                if ($stmt2 !== FALSE) {
                    $stmt2->bind_param('i', $nro_doc);
                    $stmt2->execute();
                    $res2 = $stmt2->get_result();
                    $encontrados2 = $res2->num_rows;
                    $stmt2->close();
                    $mysqli2->close();
                    if ($encontrados2 > 0) {
                        $pendientes2 = $res2->fetch_all(MYSQLI_ASSOC);
                    }
                }
                $pendientes3 = $pendientes;
                foreach ($pendientes2 as $pendiente) {
                    $existe = false;
                    foreach ($pendientes as $pend) {
                        if(in_array($pendiente['Idorden'],$pend)){
                            $existe = true;
                        }
                    }
                    if(!$existe){
                        $pendiente["prioridad"]=0;
                        array_push($pendientes3, $pendiente);
                    }
                }
                foreach ($pendientes3 as $pendiente => $vpendiente) {
                    if($vpendiente['estado']===9){
                        unset($pendientes3[$pendiente]);
                    }
                }
                $prioridad = array_column($pendientes3, 'prioridad');
                $prioridadA = $prioridad;
                array_multisort($prioridad, SORT_ASC, $pendientes3);
                $encontrados = count($pendientes3);
                $arr = array('exito'=>true, 'msg'=>'', 'encontrados'=>$encontrados, $pendientes3);
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }

    public function listarTerminados()
    {
        $arr = array('exito' => false, 'msg' => 'Error al listar pendientes');
        try {
            $nro_doc = $this->__get('nro_doc');
            $sql = 'SELECT ordenes.Idorden, ordenes.estado, ordenes.idcliente, clientes.razonsocial, ordenes.fecha, ordenes.requerimiento, ordenes.fechaprometido FROM ordenes JOIN clientes ON ordenes.idcliente = clientes.idcliente WHERE ordenes.idcomponente=20 AND (ordenes.estado = 2 OR ordenes.estado = 3 OR ordenes.estado = 5) AND replace(clientes.cuit,"-","") = ? ORDER BY ordenes.estado';
            $mysqli = ConexionWeb::abrir();
            $stmt = $mysqli->prepare($sql);
            if ($stmt !== FALSE) {
                $stmt->bind_param('i', $nro_doc);
                $stmt->execute();
                $res = $stmt->get_result();
                $encontrados = $res->num_rows;
                $stmt->close();
                $mysqli->close();
                if ($encontrados > 0) {
                    $terminados = $res->fetch_all(MYSQLI_ASSOC);
                    $arr = array('exito' => true, 'msg' => '', 'encontrados' => $encontrados, $terminados);
                } else {
                    $arr = array('exito' => true, 'msg' => '', 'encontrados' => $encontrados);
                }
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }

    public function agregar()
    {
        $arr = array('exito' => false, 'msg' => 'Error al agregar');
        try {
            $fecha = $this->__get('fecha');
            $nro_doc = $this->__get('nro_doc');
            $requerimiento = $this->__get('requerimiento');
            $prioridad = $this->__get('prioridad');
            $estado = $this->__get('estado');
            $sql = 'INSERT INTO requerimientos(fecha, nro_doc, requerimiento, prioridad, estado) VALUES (?,?,?,?,?)';
            $mysqli = Conexion::abrir();
            $stmt = $mysqli->prepare($sql);
            if ($stmt !== FALSE) {
                $stmt->bind_param('sisii', $fecha, $nro_doc, $requerimiento, $prioridad, $estado);
                $stmt->execute();
                $stmt->close();
                $id_requerimiento = $mysqli->insert_id;
                $mysqli->close();
                $arr = array('exito' => true, 'msg' => '', 'id_requerimiento'=>$id_requerimiento);
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }

    public function actualizar()
    {
        $arr = array('exito' => false, 'msg' => 'Error al actualizar');
        try {
            $Idorden = $this->__get('Idorden');
            $nro_doc = $this->__get('nro_doc');
            $requerimiento = $this->__get('requerimiento');
            $sql = 'UPDATE requerimientos SET requerimiento=? WHERE (requerimientos.Idorden=? OR requerimientos.id_requerimiento=?) AND requerimientos.nro_doc = ?';
            $mysqli = Conexion::abrir();
            $stmt = $mysqli->prepare($sql);
            if ($stmt !== FALSE) {
                $stmt->bind_param('siii', $requerimiento, $Idorden, $Idorden, $nro_doc);
                $stmt->execute();
                $actualizados = $stmt->affected_rows;
                $stmt->close();
                $mysqli->close();
                if($actualizados>0){
                    $arr = array('exito' => true, 'msg' => '');
                }else{
                    $arr['msg'] = $actualizados;
                }
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }

    public function cancelar(){
        $arr = array('exito' => false, 'msg' => 'Error al cancelar');
        try {
            $Idorden = $this->__get('Idorden');
            $nro_doc = $this->__get('nro_doc');
            $estado = 9;
            $sql = 'UPDATE requerimientos SET estado=? WHERE (requerimientos.Idorden=? OR requerimientos.id_requerimiento=?) AND requerimientos.nro_doc = ?';
            $mysqli = Conexion::abrir();
            $stmt = $mysqli->prepare($sql);
            if ($stmt !== FALSE) {
                $stmt->bind_param('iiii', $estado, $Idorden, $Idorden, $nro_doc);
                $stmt->execute();
                $actualizados = $mysqli->affected_rows;
                $stmt->close();
                $mysqli->close();
                if($actualizados>0){
                    $arr = array('exito' => true, 'msg' => '');
                }else{
                    $arr['msg'] = $actualizados."-".$Idorden."-".$nro_doc;
                }
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }
    function verificarRequerimiento(){
        $arr = array('exito' => false, 'msg' => 'Error al verificar');
        try {
            $Idorden = $this->__get('Idorden');
            $nro_doc = $this->__get('nro_doc');
            $sql = 'SELECT * FROM requerimientos WHERE (requerimientos.Idorden=? OR requerimientos.id_requerimiento=?) AND requerimientos.nro_doc = ?';
            $mysqli = Conexion::abrir();
            $stmt = $mysqli->prepare($sql);
            if ($stmt !== FALSE) {
                $stmt->bind_param('iii', $Idorden, $Idorden, $nro_doc);
                $stmt->execute();
                $res = $stmt->get_result();
                $encontrados = $res->num_rows;
                $stmt->close();
                $mysqli->close();
                if($encontrados>0){
                    $requerimiento = $res->fetch_assoc();
                    $arr = array('exito' => true, 'msg' => '', 'encontrados' => $encontrados, $requerimiento);
                }else{
                    $arr = array('exito' => true, 'msg' => '', 'encontrados' => $encontrados);
                }
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }
    function cargarRequerimiento(){
        $arr = array('exito' => false, 'msg' => 'Error al cargar requerimiento');
        try {
            $Idorden = $this->__get('Idorden');
            $nro_doc = $this->__get('nro_doc');
            $sql = 'SELECT ordenes.Idorden, ordenes.estado, ordenes.idcliente, ordenes.fecha, ordenes.requerimiento, ordenes.fechaprometido FROM ordenes JOIN clientes ON ordenes.idcliente = clientes.idcliente WHERE ordenes.idcomponente=20 AND ordenes.Idorden = ? AND replace(clientes.cuit,"-","") = ?';
            $mysqli = ConexionWeb::abrir();
            $stmt = $mysqli->prepare($sql);
            if ($stmt !== FALSE) {
                $stmt->bind_param('ii', $Idorden, $nro_doc);
                $stmt->execute();
                $res = $stmt->get_result();
                $encontrados = $res->num_rows;
                $stmt->close();
                $mysqli->close();
                if($encontrados>0){
                    $requerimiento = $res->fetch_assoc();
                    $sql2 = 'INSERT INTO requerimientos (Idorden, fecha, nro_doc, idcliente, requerimiento, fechaprometido, estado) VALUES(?,?,?,?,?,?,?)';
                    $mysqli2 = Conexion::abrir();
                    $stmt2 = $mysqli2->prepare($sql2);
                    if($stmt2 !== FALSE){
                        $stmt2->bind_param('isiissi', $requerimiento['Idorden'], $requerimiento['fecha'], $nro_doc, $requerimiento['idcliente'], $requerimiento['requerimiento'], $requerimiento['fechaprometido'], $requerimiento['estado']);
                        $stmt2->execute();
                        $insertado = $stmt2->affected_rows;
                        $stmt2->close();
                        $mysqli2->close();
                        if($insertado>0){
                            $arr = array('exito' => true, 'msg' => '');
                        }else{
                            $arr['msg'] = 'Insertados: '.$insertado;
                        }
                    }
                }else{
                    $arr['msg'] = 'Encontrados: ' . $encontrados;
                }
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }
    public function traerRequerimiento(){
        $arr = array('exito' => false, 'msg' => 'Error al cargar requerimiento');
        try {
            $Idorden = $this->__get('Idorden');
            $nro_doc = $this->__get('nro_doc');
            $sql = 'SELECT ordenes.Idorden, ordenes.estado, ordenes.idcliente, ordenes.fecha, ordenes.requerimiento, ordenes.fechaprometido FROM ordenes JOIN clientes ON ordenes.idcliente = clientes.idcliente WHERE ordenes.idcomponente=20 AND ordenes.Idorden = ? AND replace(clientes.cuit,"-","") = ?';
            $mysqli = ConexionWeb::abrir();
            $stmt = $mysqli->prepare($sql);
            if ($stmt !== FALSE) {
                $stmt->bind_param('ii', $Idorden, $nro_doc);
                $stmt->execute();
                $res = $stmt->get_result();
                $encontrados = $res->num_rows;
                $stmt->close();
                $mysqli->close();
                if($encontrados>0){
                    $requerimiento = $res->fetch_assoc();
                    $arr = array('exito'=>true, 'msg'=>'', 'encontrados'=>$encontrados, $requerimiento);
                }else{
                    $arr = array('exito'=>true, 'msg'=>'', 'encontrados'=>$encontrados, 'Idorden'=>$Idorden);
                }
            }
        }catch(\Exception $e){
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }
    public function actualizarPrioridad(){
        $arr = array('exito' => false, 'msg' => 'Error al actualizar la prioridad');
        try {
            $Idorden = $this->__get('Idorden');
            $nro_doc = $this->__get('nro_doc');
            $prioridad = $this->__get('prioridad');
            $sql = 'UPDATE requerimientos SET prioridad=? WHERE (requerimientos.Idorden=? OR requerimientos.id_requerimiento=?) AND requerimientos.nro_doc = ?';
            $mysqli = Conexion::abrir();
            $stmt = $mysqli->prepare($sql);
            if ($stmt !== FALSE) {
                $stmt->bind_param('iiii', $prioridad, $Idorden, $Idorden, $nro_doc);
                $stmt->execute();
                $actualizados = $stmt->affected_rows;
                $stmt->close();
                $mysqli->close();
                if($actualizados>0){
                    $arr = array('exito' => true, 'msg' => '');
                }else{
                    $arr['msg'] = $actualizados . " - ". $Idorden;
                }
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }

    public function listar()
    {
        $arr = array('exito' => false, 'msg' => 'Error al listar');
        try {
            $sql = 'SELECT * FROM requerimientos ORDER BY nro_doc'; 
            $mysqli = Conexion::abrir();
            $stmt = $mysqli->prepare($sql);
            if($stmt !==false){
                $stmt->execute();
                $res = $stmt->get_result();
                $encontrados = $res->num_rows;
                if($encontrados > 0){
                    $pendientes = $res->fetch_all(MYSQLI_ASSOC);
                    $arr = array('exito'=>true, 'msg'=>'', 'encontrados'=>$encontrados, $pendientes);
                }else{
                    $arr = array('exito'=>true, 'msg'=>'', 'encontrados'=>$encontrados);
                }
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }

    public function limpiarRequerimientos(){
        $arr = array('exito' => false, 'msg' => 'Error al limpiar la tabla');
        try {
            $sql = 'TRUNCATE TABLE requerimientos';
            $mysqli = Conexion::abrir();
            $stmt = $mysqli->prepare($sql);
            if($stmt !== FALSE){
                $stmt->execute();
                $stmt->close();
                $mysqli->close();
                $arr = array('exito' => true, 'msg' => '');
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }
}