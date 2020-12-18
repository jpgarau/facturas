<?php

use modelo\Conexion\Conexion;
use modelo\ConexionWeb\ConexionWeb;

include_once "modelo/ConexionWeb.php";
include_once "modelo/Conexion.php";

try {
    $mysqli = ConexionWeb::abrir();
    $sql = 'SELECT DISTINCT detservicios.idcliente, clientes.cuit, clientes.email, clientes.razonsocial FROM detservicios JOIN clientes ON detservicios.idcliente = clientes.idcliente ORDER BY idcliente';
    $stmt = $mysqli->prepare($sql);
    if($stmt!==FALSE){
        $stmt->execute();
        $res = $stmt->get_result();
        $rs = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $mysqli->close();
    }
        
} catch (\exception $e) {
    $error = $e->getMessage();
}
try{
    $mysqli = Conexion::abrir();
    $sql = "INSERT INTO usuarios(num_doc, password, nombre, correo, activacion, token, token_password, password_request, perfilid) VALUES (?,?,?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($sql);
    $procesados = 0;
    foreach ($rs as $cliente) {
        $num_doc            = filter_var(str_replace('-', '', $cliente['cuit']), FILTER_VALIDATE_INT);
        $password           = password_hash(filter_var(str_replace('-', '', $cliente['cuit']), FILTER_VALIDATE_INT), PASSWORD_DEFAULT);
        $nombre             = $cliente['razonsocial'];
        $correos            = explode(';', $cliente['email']);
        $correo             = filter_var(trim($correos[0]), FILTER_VALIDATE_EMAIL);
        $activacion         = 1;
        $token              = "";
        $token_password     = filter_var(str_replace('-', '', $cliente['cuit']), FILTER_VALIDATE_INT);
        $password_request   = 1;
        $perfilid           = 2;
        $stmt->bind_param('isssissii', $num_doc, $password, $nombre, $correo, $activacion, $token, $token_password, $password_request, $perfilid);
        $stmt->execute();
        $procesados ++;
    }
    $stmt->close();
    $mysqli->close();
}catch(\Exception $e){
    $procesados = $e->getMessage();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <pre>
        <?php echo $procesados;?>
    </pre>
</body>
</html>