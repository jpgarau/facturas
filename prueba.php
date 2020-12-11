<?php

use modelo\ConexionWeb\ConexionWeb;

include_once "modelo/ConexionWeb.php";
try {
    $mysqli = ConexionWeb::abrir();
    $sql = 'SELECT servicios.idservicio FROM servicios RIGHT JOIN detservicios ON servicios.idservicio = detservicios.idservicio LEFT JOIN clientes ON detservicios.idcliente = clientes.idcliente WHERE replace(clientes.cuit,"-","") = 30707940081 or clientes.nrodoc = 30707940081 order by servicios.idservicio';
    $stmt = $mysqli->prepare($sql);
    $rs = $stmt;
    if($stmt!==FALSE){
        $stmt->execute();
        $res = $stmt->get_result();
        $rs = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $mysqli->close();
    }
        
} catch (\exception $e) {
    $rs = $e->getMessage();
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
        <?php var_dump($rs);?>
    </pre>
</body>
</html>