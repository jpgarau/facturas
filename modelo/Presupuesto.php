<?php

namespace modelo\Presupuesto;

use mysqli_driver;

include_once 'modelo/Conexion.php';

class Presupuesto
{
    private $tipos_presu = ['PR'];
    private $id_presupuesto;
    private $tipo_presu;
    private $presupuesto;
    private $fecha;
    private $id_presu;
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
        $this->id_presu = 0;
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
        if ($name === 'id_presu' || $name === 'num_doc' || $name === 'estado_presu') {
            $value = filter_var($value, FILTER_VALIDATE_INT);
            if ($value === FALSE) $value = true;
        }
        if ($name === 'importe') {
            $value = filter_var($value, FILTER_VALIDATE_FLOAT);
            if ($value === FALSE) $value = true;
        }
        $this->{$name} = $value;
    }
}
