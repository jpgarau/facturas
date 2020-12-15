<?php

namespace controlador\PresupuestosC;

use modelo\Presupuesto\Presupuesto;

$dir = is_dir('modelo') ? '' : '../';

require_once $dir . 'modelo/validar.php';
require_once $dir . 'modelo/Presupuesto.php';

class PresupuestosC
{
    public function agregarPresupuesto($tipo_presu, $presupuesto, $fecha, $idpresventa, $num_doc, $importe, $nombre_pdf, $estado_presu)
    {
        $oPresupuesto = new Presupuesto();
        $oPresupuesto->__set('tipo_comp', $tipo_presu);
        $oPresupuesto->__set('presupuesto', $presupuesto);
        $oPresupuesto->__set('fecha', $fecha);
        $oPresupuesto->__set('idpresventa', $idpresventa);
        $oPresupuesto->__set('num_doc', $num_doc);
        $oPresupuesto->__set('importe', $importe);
        $oPresupuesto->__set('nombre_pdf', $nombre_pdf);
        $oPresupuesto->__set('estado_presu', $estado_presu);
        $retorno = $oPresupuesto->buscarPresupuesto();
        if ($retorno['exito'] && $retorno['encontrado'] === 0) {
            $retorno = $oPresupuesto->agregarPresupuesto();
        } elseif ($retorno['encontrado'] > 0) {
            $retorno = array('exito' => false, 'msg' => 'Presupuesto ya existente');
        }
        return $retorno;
    }

    public function listarPresupuestos($num_doc)
    {
        $oPresupuesto = new Presupuesto();
        $oPresupuesto->__set('num_doc', $num_doc);
        $retorno = $oPresupuesto->listadoPorNumDoc();
        return $retorno;
    }
}
