<?php
namespace controlador\ClienteC;

use modelo\Cliente\Cliente;


$dir = is_dir('modelo')?'':'../';

require_once $dir.'modelo/validar.php';
require_once $dir.'modelo/Cliente.php';

class ClienteC{
    public function agregarCliente($num_doc, $correo){
        $arr = array('exito'=>false, 'msg'=>'Error al agregar');
        try {
            $cliente = new Cliente();
            $cliente->__set('num_doc',$num_doc);
            $cliente->__set('correo', $correo);
            $retorno = $cliente->agregar();
            $arr = $retorno;
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }
    public function buscarCliente($num_doc){
        $arr = array('exito'=>false, 'msg'=>'Error al buscar');
        try {
            $cliente = new Cliente();
            $cliente->__set('num_doc', $num_doc);
            $retorno = $cliente->buscarCliente();
            $arr = $retorno;
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }
    public function listarServicios($num_doc){
        $arr = array('exito'=>false, 'msg'=>'Error al listar');
        try {
            $cliente = new Cliente();
            $cliente->__set('num_doc', $num_doc);
            $retorno = $cliente->listarServicios();
            $arr = $retorno;
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }
}