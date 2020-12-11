<?php

namespace modelo\Servicio;

use modelo\ConexionWeb\ConexionWeb;
use mysqli_driver;

include_once 'modelo/ConexionWeb.php';

class Servicio
{
    private $idservicio;
    private $descripcion;

    public function __construct()
    {
        $driver = new mysqli_driver();
        $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;
        $this->idservicio = 0;
        $this->descripcion = '';
    }

    public function getIdServicio()
    {
        return $this->idservicio;
    }
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    public function setIdServicio($idservicio)
    {
        $this->idservicio = $idservicio;
    }
    public function setDescripcion($descripcion)
    {
        $descripcion = trim($descripcion);
        $descripcion = filter_var($descripcion, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if ($descripcion === FALSE || is_null($descripcion) || strlen($descripcion) === 0) $descripcion = true;
        $this->descripcion = $descripcion;
    }

    public function listar()
    {
        $arr = array('exito' => false, 'msg' => 'Error al listar');
        try {
            $sql = 'SELECT * FROM servicios';
            $mysqli = ConexionWeb::abrir();
            $stmt = $mysqli->prepare($sql);
            if ($stmt !== FALSE) {
                $stmt->execute();
                $rs = $stmt->get_result();
                $encontrados = $rs->num_rows;
                $stmt->close();
                $mysqli->close();
                if ($encontrados > 0) {
                    $servicios = $rs->fetch_all(MYSQLI_ASSOC);
                    $arr = array('exito' => true, 'msg' => '', 'encontrados' => $encontrados, $servicios);
                }
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }
}
