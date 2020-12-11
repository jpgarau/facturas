<?php

namespace modelo\Comprobante;

use modelo\Conexion\Conexion;
use mysqli_driver;

require_once 'validar.php';
require_once 'Conexion.php';

class Comprobante
{
    private $tipos_comp = ['FC', 'NC', 'ND'];
    private $id_comprobante;
    private $tipo_comp;
    private $comprobante;
    private $fecha;
    private $id_fact_venta;
    private $num_doc;
    private $importe;
    private $nombre_pdf;
    private $estado_fact;

    public function __construct()
    {
        $driver = new mysqli_driver();
        $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;
        $this->id_comprobante = 0;
        $this->tipo_comp = '';
        $this->comprobante = '';
        $this->fecha = '';
        $this->id_fact_venta = 0;
        $this->num_doc = 0;
        $this->importe = 0;
        $this->nombre_pdf = '';
        $this->estado_fact = 0;
    }

    // getters y setters

    public function __get($name)
    {
        return $this->{$name};
    }
    public function __set($name, $value)
    {
        if ($name === 'tipo_comp') {
            $value = trim($value);
            $value = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if (!in_array($value, $this->tipos_comp, true)) {
                $value = true;
            }
        }
        if ($name === 'comprobante' || $name === 'fecha' || $name === 'nombre_pdf') {
            $value = trim($value);
            $value = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if ($value === FALSE || is_null($value || strlen($value) === 0)) $value = true;
        }
        if ($name === 'id_fact_venta' || $name === 'num_doc' || $name === 'estado_fact') {
            $value = filter_var($value, FILTER_VALIDATE_INT);
            if ($value === FALSE) $value = true;
        }
        if ($name === 'importe') {
            $value = filter_var($value, FILTER_VALIDATE_FLOAT);
            if ($value === FALSE) $value = true;
        }
        $this->{$name} = $value;
    }

    public function agregarComprobante()
    {
        $arr = array('exito' => false, 'msg' => 'Error al agregar');
        try {
            $tipo_comp = $this->__get('tipo_comp');
            $comprobante = $this->__get('comprobante');
            $fecha = $this->__get('fecha');
            $id_fact_venta = $this->__get('id_fact_venta');
            $num_doc = $this->__get('num_doc');
            $importe = $this->__get('importe');
            $nombre_pdf = $this->__get('nombre_pdf');
            $estado_fact = $this->__get('estado_fact');
            if (!(is_bool($tipo_comp) || is_bool($comprobante) || is_bool($fecha) || is_bool($id_fact_venta) || is_bool($num_doc) || is_bool($importe) || is_bool($nombre_pdf) || is_bool($estado_fact))) {
                $sql = 'INSERT INTO comprobantes (tipo_comp, comprobante, fecha, id_fact_venta, num_doc, importe, nombre_pdf, estado_fact) VALUES(?,?,?,?,?,?,?,?)';
                $mysqli = Conexion::abrir();
                $stmt = $mysqli->prepare($sql);
                if ($stmt !== FALSE) {
                    $stmt->bind_param('sssiidsi', $tipo_comp, $comprobante, $fecha, $id_fact_venta, $num_doc, $importe, $nombre_pdf, $estado_fact);
                    $stmt->execute();
                    $stmt->close();
                    $mysqli->close();
                    $arr = array('exito' => true, 'msg' => '');
                }
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }

    public function listadoPorNumDoc()
    {
        $arr = array('exito' => false, 'msg' => 'Error al listar');
        try {
            $num_doc = $this->__get('num_doc');
            $sql = 'SELECT * FROM comprobantes WHERE num_doc = ? ORDER BY fecha DESC';
            $mysqli = Conexion::abrir();
            $stmt = $mysqli->prepare($sql);
            if ($stmt !== FALSE) {
                $stmt->bind_param('i', $num_doc);
                $stmt->execute();
                $res = $stmt->get_result();
                $encontrados = $res->num_rows;
                $stmt->close();
                $mysqli->close();
                $comprobantes = $res->fetch_all(MYSQLI_ASSOC);
                $arr = array('exito' => true, 'msg' => '', 'encontrados' => $encontrados, $comprobantes);
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }

    public function buscarComprobante()
    {
        $arr = array('exito' => false, 'msg' => 'Error al buscar');
        try {
            $id_fact_venta = $this->__get('id_fact_venta');
            $sql = 'SELECT * FROM comprobantes WHERE id_fact_venta=? LIMIT 1';
            $mysqli = Conexion::abrir();
            $stmt = $mysqli->prepare($sql);
            if ($stmt !== FALSE) {
                $stmt->bind_param('i', $id_fact_venta);
                $stmt->execute();
                $rs = $stmt->get_result();
                $encontrado = $rs->num_rows;
                if ($encontrado > 0) {
                    $comprobante = $rs->fetch_assoc();
                    $arr = array('exito' => true, 'msg' => '', 'encontrado' => $encontrado, $comprobante);
                } else {
                    $arr = array('exito' => true, 'msg' => 'No encontrado', 'encontrado' => $encontrado);
                }
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }

    public function realizarMantenimiento()
    {
        $arr = array('exito' => false, 'msg' => 'Error al realizar el mantenimiento');
        try {
            $sql = 'SELECT id_comprobante, nombre_pdf FROM `comprobantes` WHERE `fecha` < DATE_SUB(NOW(),INTERVAL 1 YEAR)';
            $mysqli = Conexion::abrir();
            $stmt = $mysqli->prepare($sql);
            if ($stmt !== FALSE) {
                $stmt->execute();
                $rs = $stmt->get_result();
                $stmt->close();
                $encontrados = $rs->num_rows;
                if ($encontrados > 0) {
                    $resultado = $rs->fetch_all(MYSQLI_ASSOC);
                    $sql = 'DELETE FROM comprobantes WHERE ';
                    $contador = 0;
                    $borrados = 0;
                    foreach ($resultado as $borrar) {
                        $id_comprobante = $borrar['id_comprobante'];
                        $nombre_pdf = $borrar['nombre_pdf'];
                        $sql .= "id_comprobante = $id_comprobante ";
                        $contador++;
                        if ($contador < $encontrados) {
                            $sql .= 'OR ';
                        }
                        if (file_exists("./pdf/" . $nombre_pdf)) {
                            unlink("./pdf/" . $nombre_pdf);
                            $borrados++;
                        }
                    }
                    $arr = array('exito' => true, 'msg' => '');
                }
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }

    public function actualizarEstado()
    {
        $arr = array('exito' => false, 'msg' => 'Error al actualizar el estado');
        try {
            $id_fact_venta = $this->__get('id_fact_venta');
            $estado_fact = $this->__get('estado_fact');
            $sql = 'UPDATE comprobantes SET estado_fact = ? WHERE id_fact_venta = ?';
            $mysqli = Conexion::abrir();
            $stmt = $mysqli->prepare($sql);
            if ($stmt !== FALSE) {
                $stmt->bind_param('ii', $estado_fact, $id_fact_venta);
                $stmt->execute();
                $rs = $mysqli->info;
                $rs = explode(' ',$rs)[2];
                $stmt->close();
                $mysqli->close();
                if ($rs > 0) {
                    $arr = array('exito' => true, 'msg' => '');
                }else{
                    $arr = array('exito' => false, 'msg' => 'No se encontro el comprobante');
                }
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }
}