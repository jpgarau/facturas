<?php

use controlador\UsuarioC\UsuarioC;
use controlador\ClienteC\ClienteC;

$dir = is_dir('modelo')?'':'../';
require_once $dir.'modelo/Conexion.php';
require_once $dir.'funciones/funciones.php';
require_once $dir.'controlador/UsuarioC.php';
require_once $dir.'controlador/ClienteC.php';
$errors = array();

if(!empty($_POST)){
    if(isset($_POST['registrar'])){
        $activo = 0;
        $perfilid = 2;
        // $secret = '6LcHrtoZAAAAAGlQ0S9UvrOYtEm3M0KN1_WFPCMz';
        if(!empty($_POST['txtNombre'])) {
            $nombre = trim($_POST['txtNombre']);
            $nombre = filter_var($nombre, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if($nombre === FALSE || is_null($nombre) || strlen($nombre)===0){
                $nombre = '';
                $errors[] = 'Debe ingresar un nombre válido';
            }
        }else{
            $errors[] = "Debe completar el campo Nombre/Razón Social";
        }
        if(!empty($_POST['txtNumDoc'])) {
            $numDoc = $_POST['txtNumDoc'];
            $numDoc = filter_var($numDoc, FILTER_VALIDATE_INT);
            if($numDoc === FALSE){
                $numDoc = '';
                $errors[] = 'Debe ingresar un número de documento/CUIT válido';
            }else{
                $usuarioC = new UsuarioC;
                $retorno = $usuarioC->buscarxDoc($numDoc);
                if($retorno['encontrados']>0){
                    $errors[] = 'Ya existe una cuenta con estos datos';
                }
            }
        }else{
            $errors[] = "Debe completar el campo Nº Documento/CUIT";
        }
        if(!empty($_POST['txtPassword'])) {
            $password = $_POST['txtPassword'];
        }else{
            $errors[] = "Debe completar el campo Contraseña";
        }
        if(!empty($_POST['txtRPassword'])) {
            $rPassword = $_POST['txtRPassword'];
        }else{
            $errors[] = "Debe completar el campo Reingrese Contraseña";
        }
        if(isset($password) && isset($rPassword)){
            if (strcmp($password, $rPassword) !== 0){
                $errors[] = "Las contraseñas no son iguales";
            }
        }
        // if(!empty($_POST['g-recaptcha-response'])) {
        //     $captcha = $_POST['g-recaptcha-response'];
        // }else{
        //     $errors[] = "Debe marcar la casilla de captcha";
        // }
        if (count($errors) == 0){
            // $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$captcha");

            // $arr = json_decode($response, TRUE);
            // if($arr['success']){
                $oClienteC = new ClienteC();
                $retorno = $oClienteC->buscarCliente($numDoc);
                if($retorno['exito'] && $retorno['encontrado']>0){
                    $email = $retorno[0]['correo'];
                    $pass_hash = hashPassword($password);
                    $token = generateToken();
                    $registro = registraUsuario($numDoc, $pass_hash, $nombre, $email, $activo, $token, $perfilid);
                    if ($registro > 0) {
                        $url = 'http://'.$_SERVER['SERVER_NAME'].'/facturas/vista/activar.php?id='.$registro.'&val='.$token;
                        $asunto = 'Activar Cuenta - Agontech Clientes Web';
                        $cuerpo = "<b>Estimado $nombre:</b> <br /><br />Para continuar con el proceso de registro, es indispensable de click en el siguiente link <b><a href='$url'>Activar Cuenta</a></b>";
                        if(enviarEmail($email, $nombre, $asunto, $cuerpo)){
                            echo "Para terminar el proceso de registro siga las intrucciones que le hemos enviado a la dirección de correo electronico: $email";
                            echo "<br><a href='/facturas'>Iniciar Session</a>";
                            exit;
                        }else{
                            $errors[] = "Error al enviar Email";
                        }
                    } else {
                        $errors[] = 'Error al registrar';
                    } 
                }elseif(!$retorno['exito'] && $retorno['encontrado']===0){
                    $errors[] = 'No se encontro DNI/CUIT en la base de clientes.';
                }else{
                    $errors[] = 'Hubo un error inesperado al buscar el DNI/CUIT';
                }
            // }else {
            //     $errors[] = 'Error al comprobar Captcha';
            // }
        }

    }
}
require_once 'header.php';
?>
<div class="login">
    <div class="container p-4">
        <div class="row justify-content-center">
            <div class="card col-sm-12 col-md-10 col-lg-8 text-center p-0">
                <div class="card-header bg-dark text-white">
                    <h3 class="h3">Registrarme</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="row row-cols-1 row-cols-md-2">
                            <div class="form-group col">
                                <label for="txtNombre" class="form-label">Nombre o Razón Social</label>
                                <input type="text" name="txtNombre" id="txtNombre" class="form-control" value="<?php echo isset($nombre)?$nombre:'';?>" title="Ingrese su nombre y apellido o Razon Social">
                            </div>
                            <div class="form-group col">
                                <label for="txtNumDoc" class="form-label">Nº Documento/CUIT</label>
                                <input type="text" name="txtNumDoc" id="txtNumDoc" class="form-control" value="<?php echo isset($numDoc)?$numDoc:'';?>" title="Ingrese su Nº de documento o CUIT">
                            </div>
                            <div class="form-group col">
                                <label for="txtPassword" class="form-label">Contraseña</label>
                                <input type="password" name="txtPassword" id="txtPassword" class="form-control" title="Ingrese una contraseña">
                            </div>
                            <div class="form-group col">
                                <label for="txtRPassword" class="form-label">Reingrese Contraseña</label>
                                <input type="password" name="txtRPassword" id="txtRPassword" class="form-control" title="Vuelva a ingresar la contraseña">
                            </div>
                            <div class="form-group col">
                                <!-- <div class="g-recaptcha" data-sitekey="6LcHrtoZAAAAAL5a8LfPwr2kMcixkV-VBLqIoE4X"></div> -->
                            </div>
                        </div>
                        <?php echo resultBlock($errors); ?>
                        <div class="d-flex justify-content-between">
                                <a href="/facturas" class="btn btn-danger">Cancelar</a>
                                <button type="submit" name="registrar" id="registrar" class="btn btn-primary" onclick="cargando();">Registrarme</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
require_once 'footer.php';
?>
<script>
    function cargando(){
        let boton=document.getElementById('registrar');
        boton.innerHTML = "<i class='fas fa-spinner fa-pulse'></i> Registrando...";
    }
</script>