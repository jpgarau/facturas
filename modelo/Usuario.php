<?php

namespace modelo\Usuario;

use modelo\Conexion\Conexion;
use mysqli_driver;

class Usuario
{
    private $id;
    private $num_doc;
    private $password;
    private $nombre;
    private $correo;
    private $last_session;
    private $activacion;
    private $token;
    private $token_password;
    private $password_request;
    private $perfilid;

    public function __construct()
    {
        $driver = new mysqli_driver();
        $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;
        $this->id = 0;
        $this->num_doc = '';
        $this->password = '';
        $this->nombre = '';
        $this->correo = '';
        $this->last_session = null;
        $this->activacion = 0;
        $this->token = '';
        $this->token_password = null;
        $this->password_request = null;
        $this->perfilid = 0;
    }

    // get y set

    public function __get($name)
    {
        return $this->{$name};
    }

    public function __set($name, $value)
    {
        if ($name === 'usuario' || $name === 'password' || $name === 'nombre' || $name === 'token') {
            $value = trim($value);
            $value = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if ($value === FALSE || is_null($value) || strlen($value) === 0) $value = true;
        }
        if ($name === 'correo') {
            $value = trim($value);
            $value = filter_var($value, FILTER_SANITIZE_EMAIL);
            if ($value === FALSE || is_null($value) || strlen($value) === 0) $value = true;
        }
        if ($name === 'activacion' || $name === 'perfilid') {
            $value = filter_var($value, FILTER_VALIDATE_INT);
            if ($value === FALSE) $value = true;
        }
        if ($name === 'last_session' || $name === 'token_password') {
            $value = trim($value);
            $value = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if ($value === FALSE) $value = null;
        }
        if ($name === 'password_request') {
            $value = filter_var($value, FILTER_VALIDATE_INT);
            if ($value === FALSE) $value = null;
        }

        $this->{$name} = $value;
    }

    public function buscarDoc()
    {
        $arr = array('exito' => false, 'msg' => 'Error al buscar el Documento');
        try {
            $num_doc = $this->__get('num_doc');
            $sql = 'SELECT * FROM usuarios WHERE num_doc=?';
            $mysqli = Conexion::abrir();
            $stmt  = $mysqli->prepare($sql);
            if ($stmt !== FALSE) {
                $stmt->bind_param('s', $num_doc);
                $stmt->execute();
                $resultado = $stmt->get_result();
                $encontrados = $resultado->num_rows;
                $stmt->close();
                $mysqli->close();
                $usuario = $resultado->fetch_assoc();
                $arr = array('exito' => true, 'msg' => '', 'encontrados' => $encontrados, $usuario);
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }

    public function verificarUsuario($num_doc, $pass)
    {
        $arr = array('exito' => false, 'msg' => 'Error al verificar');
        try {
            $error = false;
            $num_doc = trim($num_doc);
            $num_doc = filter_var($num_doc, FILTER_VALIDATE_INT);
            if ($num_doc === FALSE || is_null($num_doc) || strlen($num_doc) === 0) $error = true;
            $pass = trim($pass);
            $pass = filter_var($pass, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if ($pass === FALSE || is_null($pass) || strlen($pass) === 0 || $error === true) $error = true;
            if (!$error) {
                $sql = "SELECT * FROM usuarios WHERE num_doc = ? AND activacion = 1";
                $mysqli = Conexion::abrir();
                $stmt = $mysqli->prepare($sql);
                if ($stmt !== FALSE) {
                    $stmt->bind_param('s', $num_doc);
                    $stmt->execute();
                    $rs = $stmt->get_result();
                    $stmt->close();
                    $arr = array('exito' => true, 'msg' => 'Cuenta inexistente o no activada', 'encontrado' => false);
                    while ($fila = $rs->fetch_array(MYSQLI_ASSOC)) {
                        $error = password_verify($pass, $fila['password']);
                        if ($error) {
                            unset($fila['password']);
                            $arr = array('exito' => true, 'msg' => '', 'encontrado' => true, $fila);
                            break;
                        } else {
                            $arr = array('exito' => true, 'msg' => 'Número de documento/CUIT o contraseña incorrecta', 'encontrado' => false);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }

    public function cambiarPass()
    {
        $arr = array('exito' => false, 'msg' => 'Error al cambiar el password');
        try {
            $id = $this->__get('id');
            $password = $this->__get('password');
            $token_password = $this->__get('token_password');
            $sql = 'UPDATE usuarios SET password = ?, token_password="", password_request=0 WHERE id = ? AND token_password = ?';
            $mysqli = Conexion::abrir();
            $stmt = $mysqli->prepare($sql);
            if ($stmt !== FALSE) {
                $stmt->bind_param('sis', $password, $id, $token_password);
                $stmt->execute();
                $resultado = $mysqli->affected_rows;
                $stmt->close();
                $mysqli->close();
                if ($resultado > 0) {
                    $arr = array('exito' => true, 'msg' => '');
                }
            }
        } catch (\Exception $e) {
            $arr['msg'] = $e->getMessage();
        }
        return $arr;
    }
}
