<?php

namespace controlador\ComprobantesC;

use modelo\Comprobante\Comprobante;

$dir = is_dir('modelo') ? '' : '../';

require_once $dir . 'modelo/validar.php';
require_once $dir . 'modelo/Comprobante.php';

class ComprobantesC
{
    public function agregarComprobante($tipo_comp, $comprobante, $fecha, $id_fact_venta, $num_doc, $importe, $nombre_pdf, $estado_fact)
    {
        $oComprobante = new Comprobante();
        $oComprobante->__set('tipo_comp', $tipo_comp);
        $oComprobante->__set('comprobante', $comprobante);
        $oComprobante->__set('fecha', $fecha);
        $oComprobante->__set('id_fact_venta', $id_fact_venta);
        $oComprobante->__set('num_doc', $num_doc);
        $oComprobante->__set('importe', $importe);
        $oComprobante->__set('nombre_pdf', $nombre_pdf);
        $oComprobante->__set('estado_fact', $estado_fact);
        $retorno = $oComprobante->buscarComprobante();
        if ($retorno['exito'] && $retorno['encontrado'] === 0) {
            $retorno = $oComprobante->agregarComprobante();
        } elseif ($retorno['encontrado'] > 0) {
            $retorno = array('exito' => false, 'msg' => 'Comprobante ya existente');
        }
        return $retorno;
    }

    public function listarComprobantes($num_doc)
    {
        $oComprobante = new Comprobante();
        $oComprobante->__set('num_doc', $num_doc);
        $retorno = $oComprobante->listadoPorNumDoc();
        return $retorno;
    }

    public function mantenimiento()
    {
        $oComprobante = new Comprobante();
        $retorno = $oComprobante->realizarMantenimiento();
        return $retorno;
    }

    public function actualizarEstadoFact($id_fact_venta, $estado_fact)
    {
        $oComprobante = new Comprobante();
        $oComprobante->__set('id_fact_venta', $id_fact_venta);
        $oComprobante->__set('estado_fact', $estado_fact);
        $retorno = $oComprobante->actualizarEstado();
        return $retorno;
    }
}
