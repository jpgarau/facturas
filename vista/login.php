<?php

use controlador\UsuarioC\UsuarioC;

$dir = is_dir('modelo')?'':'../';
require $dir.'modelo/validar.php';
require_once $dir.'funciones/funciones.php';
require_once $dir.'controlador/UsuarioC.php';
$errors = array();

if(isset($_GET['registro'])){
    header('Location: /facturas/vista/registro.php');
    die();
}

if(!empty($_POST)){
    $numDoc = filter_var(trim($_POST['txtNumDoc']), FILTER_VALIDATE_INT);
    $password = filter_var(trim($_POST['txtPassword']), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if(!empty($numDoc) && !empty($password)){
        if(!is_null($numDoc) && !is_null($password) && $numDoc!==FALSE && $password!==FALSE){
            $usuarioC = new UsuarioC;
            $ret = $usuarioC->verificar($numDoc, $password);
            if(is_bool($ret)){
                $errors[] = 'Error al verificar';
            }else{
                if($ret['exito']){
                    if($ret['encontrado']){
                        $_SESSION['usuario'] = $ret[0]['nombre'];
                        $_SESSION['userProfile'] = $ret[0];
                        header('Location: /facturas');
                    }else{
                        $errors[] = $ret['msg'];
                    }
                }else{
                    $errors[] = $ret['msg'];
                }
            }
        }else{
            $errors[] = 'Los datos ingresados no son validos';
        }
    }else{
        $errors[] = 'Debe completar todos los campos';
    }
}

require_once $dir.'vista/header.php';

if (!isset($_SESSION['usuario']) || ($_SESSION['userProfile']['password_request'])===1) {
?>
<div class="login">
    <div class="container p-4">
        <div class="row justify-content-center">
            <div class="card col-sm-6 col-md-6 col-lg-3 text-center p-0">
                <div class="card-header bg-dark ">
                    <div class="logo-img">
                        <img src="/facturas/img/logo.png" alt="Logo agontech">
                    </div>
                <h3 class="h3 text-white font-weight-bold">Iniciar Sesión</h3>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="form-group">
                            <label for="txtNumDoc" class="form-label">Nº Documento/CUIT</label>
                            <input type="text" name="txtNumDoc" id="txtNumDoc" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="txtPassword" class="form-label">Contraseña</label>
                            <input type="password" name="txtPassword" id="txtPassword" class="form-control">
                        </div>
                        <?php echo resultBlock($errors); ?>
                        <div class="form-group d-flex justify-content-center">
                            <button type="submit" name="login" class="btn btn-primary btn-block">Iniciar</button>
                        </div>
                    </form>
                    <div class="form-group">
                        <form action="" class="d-flex justify-content-center" method="get">
                            <button type="submit" name="registro" class="btn btn-success btn-block">Registrarme</button>
                        </form>
                    </div>
                    <div>
                        <a href="/facturas/vista/recuperar.php" class="text-muted text-decoration-none"><small><i>*Olvide mi contraseña</i></small></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
}
require_once 'footer.php';
?>
</body>
</html>