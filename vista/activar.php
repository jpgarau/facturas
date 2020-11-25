<?php
	$dir = is_dir('modelo')?'':'../';
    
    require $dir.'funciones/funciones.php';
	
	$mensaje = null;

	if(isset($_GET["id"]) AND isset($_GET['val'])){
		$idUsuario = $_GET['id'];
		$token = $_GET['val'];

		$mensaje = validaIdToken($idUsuario, $token);
	}
?>

<!doctype html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Registro</title>
		<link rel="stylesheet" href="../extras/css/bootstrap.min.css" >
        <link rel="stylesheet" href="../extras/css/all.css">
        <link rel="stylesheet" href="../css/estilos.css">
		
	</head>
	
	<body style="width: 100%; height:100vh; background-image:url(../img/login.jpg); background-size:cover; background-repeat:no-repeat;">
        <div class="container d-flex h-100 justify-content-center align-items-center">
            <div class="jumbotron text-center">
                
                <h1><?php echo $mensaje; ?></h1>
				
				<br />
				<p><a class="btn btn-primary btn-lg" href="/facturas" role="button">Iniciar Sesi&oacute;n</a></p>
			</div>
        </div>
        <script src="../extras/js/jquery-3.5.1.min.js"></script>
        <script src="../extras/js/popper.min.js"></script>
        <script src="../extras/js/bootstrap.bundle.min.js"></script>
	</body>
</html>