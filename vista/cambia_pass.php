<?php

use controlador\UsuarioC\UsuarioC;

$dir = is_dir('modelo') ? '' : '../';

require_once $dir . 'modelo/validar.php';
require $dir . 'funciones/funciones.php';
require $dir . 'controlador/UsuarioC.php';
$errors = array();
$user_id = null;
$token = null;

if(isset($_SESSION['usuario']) && isset($_SESSION['userProfile'])){
	$user_id = $_SESSION['userProfile']['id'];
	$correo = $_SESSION['userProfile']['correo'];
	$token = $_SESSION['userProfile']['token_password'];
}else{
	if (empty($_GET['user_id'])) {
		header('Location: /facturas');
	}
	if (empty($_GET['token'])) {
		header('Location: /facturas');
	}
	
	$user_id = filter_var($_GET['user_id'], FILTER_VALIDATE_INT);
	$token = filter_var($_GET['token'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	
	if (!verificaTokenPass($user_id, $token)) {
		echo 'No se pudo verificar los Datos';
		echo "<br /><a href='/facturas'>Iniciar Session</a>";
		exit;
	}

	$usuarioC = new UsuarioC();
	$retorno = $usuarioC->buscarUsuario($user_id);
	if($retorno['exito']){
		$correo = $retorno[0]['correo'];
	}
}

if (!empty($_POST)) {
	$user_id = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
	$correo = filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL);
	$token = filter_var($_POST['token'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	$password = filter_var($_POST['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	$rPassword = filter_var($_POST['rPassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

	if ($correo === FALSE || is_null($correo) || strlen($correo) === 0) {
		$errors[] = "Error en el correo. Verifique";
	}

	if (strcmp($password, $rPassword) !== 0) {
		$errors[] = 'Las contraseñas no coinciden';
	}

	if(count($errors)==0){	
		$pass_hash = hashPassword($password);
		$ousuarioC = new UsuarioC;
		$rs = $ousuarioC->cambioPass($pass_hash, $correo, $user_id, $token);
		if (!is_bool($rs)) {
			if ($rs['exito']) {
				echo "<b>Password modificado</b>";
				echo "<br /><a href='/facturas/vistas/logout.php'>Iniciar Session</a>";
				exit;
			} else {
				$errors[] = "Error al modificar el Password";
			}
		} else {
			$errors[] = "Error al modificar el Password";
		}
	}
}

?>

<!doctype html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Cambiar Password</title>

	<link rel="stylesheet" href="../extras/css/bootstrap.min.css">
	<link rel="stylesheet" href="../extras/css/all.css">
	<link rel="stylesheet" href="../css/estilos.css">

</head>

<body style="width: 100%; height:100vh; background-image:url(../img/fondo.jpg); background-size:cover; background-repeat:no-repeat;">

	<div class="container d-flex h-100 justify-content-center align-items-center">
		<div>
			<div class="card card-info">
				<div class="card-header d-flex justify-content-between">
					<div class="mr-3">Cambiar Password</div>
					<div><a href="/facturas/vista/logout.php">Iniciar Sesi&oacute;n</a></div>
				</div>

				<div class="card-body">

					<form action="" method="POST" autocomplete="off">

						<input type="hidden" id="user_id" name="user_id" value="<?php echo $user_id; ?>" />

						<input type="hidden" id="token" name="token" value="<?php echo $token; ?>" />

						<div class="form-group">
							<label for="correo" class="control-label">Correo</label>
							<input type="email" class="form-control" name="correo" value="<?php if(isset($correo)){echo $correo;}?>" required>
						</div>

						<div class="form-group">
							<label for="password" class="control-label">Nuevo Password</label>
							<input type="password" class="form-control" name="password" required>
						</div>

						<div class="form-group">
							<label for="con_password" class="control-label">Confirmar Password</label>
							<input type="password" class="form-control" name="rPassword" required>
						</div>
						<?php echo resultBlock($errors); ?>

						<div class="form-group">
							<button id="btn-login" type="submit" class="btn btn-success btn-block">Cambiar</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<script src="../extras/js/jquery-3.5.1.min.js"></script>
	<script src="../extras/js/popper.min.js"></script>
	<script src="../extras/js/bootstrap.bundle.min.js"></script>
</body>

</html>