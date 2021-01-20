<?php

use controlador\UsuarioC\UsuarioC;

$dir = is_dir('modelo')?'':'../';
require $dir.'funciones/funciones.php';
require $dir.'controlador/UsuarioC.php';

$errors = array();

if(!empty($_POST)){
	$numDoc = filter_var($_POST['numDoc'],FILTER_VALIDATE_INT);
	if(empty($numDoc)){
		$errors[] = 'Debe ingresar un número de documento/CUIT válido';
	}else{
		$usuarioC = new UsuarioC;
		$ret = $usuarioC->buscarxDoc($numDoc);
		if($ret['exito']){
			if($ret['encontrados']>0){
				$user_id = $ret[0]['id'];
				$nombre = $ret[0]['nombre'];
				$email = $ret[0]['correo'];
				$token = generaTokenPass($user_id);
				$url = 'http://'.$_SERVER["SERVER_NAME"].'/webagon3/clientes/vista/cambia_pass.php?user_id='.$user_id.'&token='.$token;
				$asunto = 'Recuperar Password - Agontech Clientes Web';
				$cuerpo = "Hola $nombre: <br /><br />Se ha solicitado un reinicio de contrase&ntilde;a <br /><br />Para restaurar la contrase&ntilde;a, visita la siguiente link: <a href='$url'>Cambiar Password</a>";
				if(enviarEmail($email, $nombre, $asunto, $cuerpo)){
					echo "Hemos enviado un correo electronico a la direccion $email para restablecer tu password. <br />";
					echo "<a href='/webagon3/clientes'>Iniciar Session</a>";
				exit;
				}else{
					$errors[] = 'Error al enviar el email';
				}
			}else{
				$errors[] = 'No se encontro un cliente con esos datos';
			}
		} 
	}
}
?>
<!doctype html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>Recuperar Password</title>
		
		<link rel="stylesheet" href="../extras/css/bootstrap.min.css" >
        <link rel="stylesheet" href="../extras/css/all.css">
        <link rel="stylesheet" href="../css/estilos.css">

		
	</head>
	
	<body style="width: 100%; height:100vh; background-image:url(../img/fondo.jpg); background-size:cover; background-repeat:no-repeat;">
		
		<div class="container d-flex h-100 justify-content-center align-items-center">    
			<div class="">                    
				<div class="card" >
					<div class="card-header bg-dark d-flex justify-content-between">
						<div class="mr-3 text-white">Recuperar Password</div>
						<div><a href="/webagon3/clientes" class="text-primary text-decoration-none">Iniciar Sesi&oacute;n</a></div>
					</div>     
					
					<div class="card-body" >
						
						<div style="display:none" id="login-alert" class="alert alert-danger col-sm-12"></div>
						
						<form method="POST" autocomplete="off">
							
							<div class="form-group">
								<label for="numDoc">Número de documento/CUIT</label>
								<input type="text" class="form-control" name="numDoc" title="Ingrese el número de documento/CUIT de la cuenta que desea recuperar. Solo los números sin guiones o puntos." required>                                        
							</div>
							
                            <?php echo resultBlock($errors); ?>
							<div class="form-group">
                                <button type="submit" class="btn btn-success btn-block">Enviar</a>
							</div>
							
						</form>
					</div>
                    <div class="card-footer">
                        <div class="">
                            <small><i>No tiene una cuenta! <a href="/webagon3/clientes/vista/registro.php" class="text-decoration-none">Registrate aquí</a></i></small>
                        </div>
                    </div>
				</div>
			</div>
        </div>
        <script src="../extras/js/jquery-3.5.1.min.js"></script>
        <script src="../extras/js/popper.min.js"></script>
        <script src="../extras/js/bootstrap.bundle.min.js"></script>
	</body>
</html>