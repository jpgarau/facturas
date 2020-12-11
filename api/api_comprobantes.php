<?php

use controlador\ComprobantesC\ComprobantesC;
use controlador\ClienteC\ClienteC;

$dir = is_dir('modelo') ? '' : '../';
require_once $dir . 'modelo/validar.php';
require_once $dir . 'controlador/ComprobantesC.php';
require_once $dir . 'controlador/ClienteC.php';

header('Content-Type: application/json; charset=UTF-8');

$metodo = $_SERVER['REQUEST_METHOD'];

$retorno = array('exito' => false, 'msg' => 'Error en la carga', 'metodo' => $metodo);

switch ($metodo) {
    case 'GET':
        header('Location: /facturas');
        die;
        break;
    case 'POST':
        try {

            if (!isset($_POST['data'])) {
                throw new Exception('No se recibio la Data a procesar.');
            }

            if (!isset($_FILES['file'])) {
                throw new Exception('No se recibio el archivo PDF, revise!!.');
            }

            if ($_FILES['file']['size'] == 0) {
                throw new Exception('Su archivo esta vacio o corrupto.');
            }

            $extensiones = array('pdf');
            $fileNameCmps = explode(".", $_FILES['file']['name']);
            if (!in_array(strtolower(end($fileNameCmps)), $extensiones)) {
                throw new Exception('Solo se permiten archivos PDF.');
            }

            $datos = json_decode($_POST['data'], true);
            $tipo_comp      = $datos['tipo_comp'];
            $comprobante    = $datos['comprobante'];
            $fecha          = preg_replace('/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/', '$3-$2-$1', $datos['fecha']);
            $id_fact_venta  = $datos['id_fact_venta'];
            $num_doc        = filter_var(str_replace('-', '', $datos['num_doc']), FILTER_VALIDATE_INT);
            $correos        = explode(';', $datos['email']);
            $correo         = filter_var(trim($correos[0]), FILTER_VALIDATE_EMAIL);
            $importe        = $datos['importe'];
            $nombre_pdf     = $datos['nombre_pdf'];
            $estado_fact    = $datos['estado_fact'];

            $clienteC = new ClienteC();
            $res = $clienteC->buscarCliente($num_doc);
            if ($res['exito'] !== true) {
                $res = $clienteC->agregarCliente($num_doc, $correo);
                if ($res['exito'] !== true) {
                    throw new Exception($res['msg']);
                }
            }

            $oComprobanteC = new ComprobantesC();
            $retorno = $oComprobanteC->agregarComprobante($tipo_comp, $comprobante, $fecha, $id_fact_venta, $num_doc, $importe, $nombre_pdf, $estado_fact);
            if ($retorno['exito']) {
                $retorno = guardarPdf();
                if ($retorno['exito']) {
                    $retorno = array('exito' => true, 'msg' => 'Subido con exito');
                } else {
                    throw new Exception($retorno['msg']);
                }
            } else {
                throw new Exception($retorno['msg']);
            }
        } catch (Exception $e) {
            header('HTTP/1.1 401');
            die(json_encode(array('exito' => false, 'msg' => $e->getMessage())));
        }

        break;
    case 'PUT':
        $datosPUT = file_get_contents("php://input");
        $datos = json_decode($datosPUT,true);
        try {
            if (!isset($datos) || count($datos)===0) {
                throw new Exception('No se recibio la Data a procesar.');
            }
            $id_fact_venta  = $datos['id_fact_venta'];
            $estado_fact    = $datos['estado_fact'];
            $oComprobanteC = new ComprobantesC();
            $retorno = $oComprobanteC->actualizarEstadoFact($id_fact_venta, $estado_fact);
            if($retorno['exito']){
                $retorno = array('exito' => true, 'msg' => 'Estado actualizado con exito');
            }else{
                throw new Exception($retorno['msg']);
            }
        } catch (Exception $e) {
            header('HTTP/1.1 401');
            die(json_encode(array('exito' => false, 'msg' => $e->getMessage())));
        }
        break;
    default:
        header('HTTP/1.1 404');
        die(json_encode(array('exito' => false, 'msg' => 'Recurso no encontrado')));
        break;
}

if ($retorno['exito'] == true) {
    header('HTTP/1.1 200');
    echo json_encode($retorno);
} else {
    header('HTTP/1.1 500');
    die(json_encode($retorno));
}

function guardarPdf()
{
    $arr = array('exito' => false, 'msg' => 'Error al subir el archivo');
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath  = $_FILES['file']['tmp_name'];
        $fileName     = $_FILES['file']['name'];
        $directorio = '../pdf/';
        $dest_path = $directorio . $fileName;
        if (!is_file($dest_path)) {
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // $contenido = file_get_contents($dest_path);
                // file_put_contents($dest_path, base64_decode($contenido));
                $arr = array('exito' => true, 'msg' => 'Subido con exito');
            } else {
                $arr['msg'] = "Error al mover el archivo";
            }
        } else {
            $arr['msg'] = "Error el archivo ya existe";
        }
    } else {
        $arr['msg'] = $_FILES['file']['error'];
    }
    return $arr;
}
