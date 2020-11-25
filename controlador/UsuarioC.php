<?php
namespace controlador\UsuarioC;

use modelo\Usuario\Usuario;

$dir = is_dir('modelo')?'':'../';

require_once $dir.'modelo/validar.php';
require_once $dir.'modelo/Usuario.php';

class UsuarioC
{
    
    public function buscarxDoc($num_doc){
        $usuario = new Usuario();
        $usuario->__set('num_doc',$num_doc);
        return $usuario->buscarDoc();
    }

    public function verificar($usuario,$password){
        $ret = false;
        $ousuario = new Usuario();
        $ret = $ousuario->verificarUsuario($usuario,$password);
        return $ret;
    }

    public function cambioPass($password, $id, $token_password){
        $ret = false;
        $ousuario = new Usuario();
        $ousuario->__set('id',$id);
        $ousuario->__set('password', $password);
        $ousuario->__set('token_password', $token_password);
        $ret = $ousuario->cambiarPass();
        return $ret;
    }
}