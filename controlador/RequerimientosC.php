<?php
namespace controlador\RequerimientosC;

use modelo\Requerimiento\Requerimiento;

$dir = is_dir('modelo')?"":"../";

require_once $dir . 'modelo/validar.php';
require_once $dir . 'modelo/Requerimiento.php';

class RequerimientosC{
    public function listarRequerimientos(){
        $oRequerimientosC = new Requerimiento();
        $retorno = $oRequerimientosC->listar();
        return $retorno;
    }
}