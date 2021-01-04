<?php

use controlador\RequerimientosC\RequerimientosC;

$dir = is_dir('modelo')?"":"../";

require_once $dir . 'modelo/validar.php';
require_once $dir . 'controlador/RequerimientosC.php';

header('Content-Type: application/json; charset=UTF-8');

$metodo = $_SERVER['REQUEST_METHOD'];

$retorno = array('exito' => false, 'msg' => 'Error en la carga', 'metodo' => $metodo);

switch ($metodo) {
    case 'GET':
        $oRequerimientosC = new RequerimientosC();
        $retorno = $oRequerimientosC->listarRequerimientos();
        break;
    
    default:
        break;
}

if ($retorno['exito'] == true) {
    header('HTTP/1.1 200');
    echo json_encode($retorno);
} else {
    header('HTTP/1.1 500');
    die(json_encode($retorno));
}