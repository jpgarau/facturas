<?php

namespace modelo\Presupuesto;

use modelo\Conexion\Conexion;
use mysqli_driver;

include_once 'modelo/Conexion.php';

class Presupuesto
{
    private $tipos_presu = ['PR'];
    private $id_presupuesto;
    private $tipo_presu;
    private $presupuesto;
    private $fecha;
    private $idpresventa;
    private $num_doc;
    private $importe;
    private $nombre_pdf;
    private $estado_presu;

    public function __construct()
    {
        $driver = new mysqli_driver();
        $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;
        $this->id_presupuesto = 0;
        $this->tipo_presu = "";
        $this->presupuesto = "";
        $this->fecha = "";
        $this->idpresventa = 0;
        $this->num_doc = 0;
        $this->importe = 0;
        $this->nombre_pdf = "";
        $this->estado_presu = 0;
    }

    public function __get($name)
    {
        return $this->{$name};
    }
    public function __set($name, $value)
    {
        if ($name === 'tipo_presu') {
            $value = trim($value);
            $value = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if (!in_array($value, $this->tipos_presu, true)) {
                $value = true;
            }
        }
        if ($name === 'presupuesto' || $name === 'fecha' || $name === 'nombre_pdf') {
            $value = trim($value);
            $value = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if ($value === FALSE || is_null($value || strlen($value) === 0)) $value = true;
        }
        if ($name === 'idpresventa' || $name === 'num_doc' || $name === 'estado_presu') {
            $value = filter_var($value, FILTER_VALIDATE_INT);
            if ($value === FALSE) $value = true;
        }
        if ($name === 'importe') {
            $value = filter_var($value, FILTER_VALIDATE_FLOAT);
            if ($value === FALSE) $value = true;
        }
        $this->{$name} = $value;
    }

    public function agregarPresupuesto()
    {
        $arr = array('exito' => false, 'msg' => 'Error al agregar');
        try {
            $tipo_presu = $this->__get('tipo_presu');
            $presupuesto = $this->__get('presupuesto');
            $fecha = $this->__get('fecha');
            $idpresventa = $this->__get('idpresventa');
            $num_doc = $this->__get('num_doc');
            $importe = $this->__get('importe');
            $nombre_pdf = $this->__get('nombre_pdf');
            $estado_presu = $this->__get('estado_presu');
            if (!(is_bool($tipo_presu) || is_bool($presupuesto) || is_bool($fecha) || is_bool($idpresventa) || is_bool($num_doc) || is_bool($importe) || is_bool($nombre_pdf) || is_bool($estado_presu))) {
                $sql = 'INSERT INTO presupuestos (tipo_presu, presupuesto, fecha, idpresventa, num_doc, importe, nombre_pdf, estado_presu) VALUES(?,?,?,?,?,?,?,?)';
                $mysqli = Conexion::abrir();
                $stmt = $mysqli->prepare($sql);
                if ($stmt !== FALSE) {
                    $stmt->bind_param('sssiidsi', $tipo_presu, $presupuesto, $fecha, $idpresventa, $num_doc, $importe, $nombre_pdf, $estado_presu);
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
            $sql = 'SELECT * FROM presupuestos WHERE num_doc = ? ORDER BY fecha DESC LIMIT 30';
            $mysqli = Conexion::abrir();
            $stmt = $mysqli->prepare($sql);
            if ($stmt !== FALSE) {
                $stmt->bind_param('i', $num_doc);
                $stmt->execute();
                $res = $stmt->get_result();
                $encontrados = $res->num_rows;
                $stmt->close();
                $mysqli->close();
                $presupuestos = $res->fetch_all(MYSQLI_ASSOC);
                $arr = array('exito' => true, 'msg' => '', 'encontrados' => $encontrados, $presupuestos);
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }
    public function buscarPresupuesto()
    {
        $arr = array('exito' => false, 'msg' => 'Error al buscar');
        try {
            $idpresventa = $this->__get('idpresventa');
            $sql = 'SELECT * FROM presupuestos WHERE idpresventa=? LIMIT 1';
            $mysqli = Conexion::abrir();
            $stmt = $mysqli->prepare($sql);
            if ($stmt !== FALSE) {
                $stmt->bind_param('i', $idpresventa);
                $stmt->execute();
                $rs = $stmt->get_result();
                $encontrado = $rs->num_rows;
                if ($encontrado > 0) {
                    $presupuesto = $rs->fetch_assoc();
                    $arr = array('exito' => true, 'msg' => '', 'encontrado' => $encontrado, $presupuesto);
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
            $sql = 'SELECT id_presupuesto, nombre_pdf FROM `presupuestos` WHERE `fecha` < DATE_SUB(NOW(),INTERVAL 1 YEAR)';
            $mysqli = Conexion::abrir();
            $stmt = $mysqli->prepare($sql);
            if ($stmt !== FALSE) {
                $stmt->execute();
                $rs = $stmt->get_result();
                $stmt->close();
                $encontrados = $rs->num_rows;
                if ($encontrados > 0) {
                    $resultado = $rs->fetch_all(MYSQLI_ASSOC);
                    $sql = 'DELETE FROM presupuestos WHERE ';
                    $contador = 0;
                    $borrados = 0;
                    foreach ($resultado as $borrar) {
                        $id_presupuesto = $borrar['id_presupuesto'];
                        $nombre_pdf = $borrar['nombre_pdf'];
                        $sql .= "id_presupuesto = $id_presupuesto ";
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
}
