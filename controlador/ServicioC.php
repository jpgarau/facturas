<?php

use modelo\Servicio\Servicio;
$dir = is_dir('modelo') ? '' : '../';
include_once $dir . 'modelo/Servicio.php';

class ServicioC
{

    public function listarServicios()
    {
        $servicio = new Servicio();
        $retorno = $servicio->listar();
        return $retorno;
    }
}
