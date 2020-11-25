<?php
$dir = is_dir('modelo')?'':'../';
use modelo\Conexion\Conexion;

require_once $dir.'modelo/Conexion.php';

	function generateToken()
	{
		$gen = md5(uniqid(mt_rand(), false));	
		return $gen;
	}
	
	function hashPassword($password) 
	{
		$hash = password_hash($password, PASSWORD_DEFAULT);
		return $hash;
	}
	
	function resultBlock($errors){
		if(count($errors) > 0)
		{
			echo "<div id='error' class='alert alert-danger text-left' role='alert'><ul class='mb-0'>";
			foreach($errors as $error)
			{
				echo "<li>".$error."</li>";
			}
			echo "</ul>";
			echo "</div>";
		}
	}
	
	function registraUsuario($numDoc, $pass_hash, $nombre, $correo, $activo, $token, $perfilid){
		
		$mysqli = Conexion::abrir();
		$sql = "INSERT INTO usuarios (num_doc, password, nombre, correo, activacion, token, perfilid) VALUES(?,?,?,?,?,?,?)";
		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('ssssisi', $numDoc, $pass_hash, $nombre, $correo, $activo, $token, $perfilid);
		
		if ($stmt->execute()){
			return $mysqli->insert_id;
			} else {
			return 0;	
		}		
	}
	
	function enviarEmail($email, $nombre, $asunto, $cuerpo){
		$dir = is_dir('modelo')?'':'../';
		require_once $dir.'PHPMailer/PHPMailerAutoload.php';
		
		$mail = new PHPMailer();
		$mail->isSMTP();
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = 'tls'; //Modificar 'tipo de seguridad' tls
		$mail->Host = 'smtp.gmail.com'; //Modificar 'dominio' smtp.gmail.com
		$mail->Port = 587; //Modificar "puerto" 587
		
		$mail->Username = 'talleragontech@gmail.com'; //Modificar 'correo emisor' talleragontech@gmail.com
		$mail->Password = 'ATTaller2018'; //Modificar ATTaller2018 'password de correo emisor'
		
		$mail->setFrom('talleragontech@gmail.com', 'Taller Agontech'); //Modificar 'correo emisor', 'nombre de correo emisor' talleragontech@gmail.com Taller Agontech
		$mail->addAddress($email, $nombre);  //correo y nombre del destinatario
		// $mail->addAttachment('archivo.ext', 'Nombre como va a aparecer')
		$mail->Subject = $asunto;
		$mail->Body    = $cuerpo;
		$mail->IsHTML(true);
		
		if($mail->send())
		return true;
		else
		return false;
	}
	
	function validaIdToken($id, $token){
		$mysqli = Conexion::abrir();
		
		$stmt = $mysqli->prepare("SELECT activacion FROM usuarios WHERE id = ? AND token = ? LIMIT 1");
		$stmt->bind_param("is", $id, $token);
		$stmt->execute();
		$stmt->store_result();
		$rows = $stmt->num_rows;
		
		if($rows > 0) {
			$stmt->bind_result($activacion);
			$stmt->fetch();
			$stmt->close();
			$mysqli->close();
			
			if($activacion == 1){
				$msg = '<i class="fas fa-exclamation-triangle fa-2x text-warning"></i><br>La cuenta ya se activo anteriormente.';
				} else {
				if(activarUsuario($id)){
					$msg = '<i class="fas fa-check-circle fa-2x text-success"></i><br>Cuenta activada.';
					} else {
					$msg = '<i class="fas fa-times-circle fa-2x text-danger"></i><br>Error al Activar Cuenta';
				}
			}
			} else {
			$msg = '<i class="fas fa-times-circle fa-2x text-danger"></i><br>No existe el registro para activar.';
		}
		return $msg;
	}
	
	function activarUsuario($id)
	{
		$mysqli = Conexion::abrir();
		
		$stmt = $mysqli->prepare("UPDATE usuarios SET activacion=1 WHERE id = ?");
		$stmt->bind_param('s', $id);
		$result = $stmt->execute();
		$stmt->close();
		$mysqli->close();
		return $result;
	}
	function lastSession($id)
	{
		$mysqli = Conexion::abrir();
		
		$stmt = $mysqli->prepare("UPDATE usuarios SET last_session=NOW(), token_password='', password_request=0 WHERE id = ?");
		$stmt->bind_param('s', $id);
		$stmt->execute();
		$stmt->close();
	}
	
	function generaTokenPass($user_id)
	{
		$mysqli = Conexion::abrir();
		
		$token = generateToken();
		
		$stmt = $mysqli->prepare("UPDATE usuarios SET token_password=?, password_request=1 WHERE id = ?");
		$stmt->bind_param('ss', $token, $user_id);
		$stmt->execute();
		$stmt->close();
		
		return $token;
	}
	
	function verificaTokenPass($user_id, $token){
		
		$mysqli = Conexion::abrir();
		
		$stmt = $mysqli->prepare("SELECT activacion FROM usuarios WHERE id = ? AND token_password = ? AND password_request = 1 LIMIT 1");
		$stmt->bind_param('is', $user_id, $token);
		$stmt->execute();
		$stmt->store_result();
		$num = $stmt->num_rows;
		
		if ($num > 0)
		{
			$stmt->bind_result($activacion);
			$stmt->fetch();
			if($activacion == 1)
			{
				return true;
			}
			else 
			{
				return false;
			}
		}
		else
		{
			return false;	
		}
	}

	function mantenimiento(){
		//realizar borrado de archivos y registros por usuario que no superen el año
		
	}
		