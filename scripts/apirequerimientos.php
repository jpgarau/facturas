<?php

use modelo\Requerimiento\Requerimiento;

$dir = is_dir('modelo') ? '' : '../';
include_once $dir . 'modelo/validar.php';

if (!isset($_SESSION['usuario'])) {
    header('HTTP/1.1 401');
    die('No Autorizado');
}
if (!peticion_ajax()) {
    header('HTTP/1.1 401');
    die(json_encode(array('exito' => false, 'msg' => 'No Autorizado')));
}
if (!isset($_POST['param'])) {
    header('HTTP/1.1 401');
    die(json_encode(array('exito' => false, 'msg' => 'No Autorizado')));
}

include_once $dir . 'modelo/Requerimiento.php';

$tarea = $_POST['param'];
if (isset($_SESSION['userProfile'])) $nro_doc = $_SESSION['userProfile']['num_doc'];
if (isset($_POST['Idorden'])) $Idorden = $_POST['Idorden'];
if (isset($_POST['fecha'])) $fecha = preg_replace('/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/', '$3-$2-$1', $_POST['fecha']);
if (isset($_POST['idcliente'])) $idcliente = $_POST['idcliente'];
if (isset($_POST['requerimiento'])) $requerimiento = $_POST['requerimiento'];
if (isset($_POST['fechaprometido'])) $fechaprometido = $_POST['fechaprometido'];
if (isset($_POST['prioridad'])) $prioridad = $_POST['prioridad'];
if (isset($_POST['estado'])) $estado = $_POST['estado'];
if (isset($_POST['prioridades'])) $prioridades = $_POST['prioridades'];

switch ($tarea) {
    case 1:
        $oRequerimiento = new Requerimiento();
        $oRequerimiento->__set('nro_doc', $nro_doc);
        $retorno = $oRequerimiento->listarPendientes();
        break;
    case 2:
        $oRequerimiento = new Requerimiento();
        $oRequerimiento->__set('nro_doc', $nro_doc);
        $retorno = $oRequerimiento->listarTerminados();
        break;
    case 3:
        $oRequerimiento = new Requerimiento();
        $oRequerimiento->__set('nro_doc', $nro_doc);
        $oRequerimiento->__set('fecha', $fecha);
        $oRequerimiento->__set('requerimiento', $requerimiento);
        $oRequerimiento->__set('prioridad', $prioridad);
        $oRequerimiento->__set('estado', $estado);
        $retorno = $oRequerimiento->agregar();
        break;
    case 4:
        $oRequerimiento = new Requerimiento();
        $oRequerimiento->__set('Idorden', $Idorden);
        $oRequerimiento->__set('nro_doc', $nro_doc);
        $oRequerimiento->__set('requerimiento', $requerimiento);
        $retorno = $oRequerimiento->verificarRequerimiento();
        if ($retorno['exito'] === true && $retorno['encontrados'] < 1) {
            $retorno = $oRequerimiento->cargarRequerimiento();
            if ($retorno['exito'] !== true) {
                break;
            }
        }
        $retorno = $oRequerimiento->actualizar();
        // $retorno = array('exito' =>false, 'idorden'=>$oRequerimiento->__get('Idorden'));
        break;
    case 5:
        $oRequerimiento = new Requerimiento();
        $oRequerimiento->__set('Idorden', $Idorden);
        $oRequerimiento->__set('nro_doc', $nro_doc);
        $retorno = $oRequerimiento->verificarRequerimiento();
        if ($retorno['exito'] === true && $retorno['encontrados'] < 1) {
            $oRequerimiento->cargarRequerimiento();
        }
        $retorno = $oRequerimiento->cancelar();
        break;
    case 6:
        $oRequerimiento = new Requerimiento();
        foreach ($prioridades as $requerimiento) {
            $Idorden = $requerimiento['Idorden'];
            $prioridad = $requerimiento['prioridad'];
            $oRequerimiento->__set('Idorden', $Idorden);
            $oRequerimiento->__set('nro_doc', $nro_doc);
            $oRequerimiento->__set('prioridad', $prioridad);
            $retorno = $oRequerimiento->verificarRequerimiento();
            if ($retorno['exito'] === true && $retorno['encontrados'] < 1) {
                $retorno = $oRequerimiento->traerRequerimiento();
                if($retorno['exito'] === true && $retorno['encontrados'] === 1){
                    if(isset($retorno[0]['prioridad'])){
                        if($retorno[0]['prioridad']===$prioridad){
                            continue;
                        }
                    }
                    $retorno = $oRequerimiento->cargarRequerimiento();
                }else{
                    continue;
                }
            }elseif($retorno['exito'] === true && $retorno['encontrados'] === 1){
                if($retorno[0]['prioridad']==$prioridad){
                    continue;
                }
            }else{
                continue;
            }
            $retorno = $oRequerimiento->actualizarPrioridad();
        }
        break;
    default:
        break;
}

if ($retorno['exito'] == true) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($retorno);
} else {
    header('HTTP/1.1 500');
    die(json_encode($retorno));
}

function peticion_ajax()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
}
