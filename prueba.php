<?php

use modelo\Conexion\Conexion;
use modelo\ConexionWeb\ConexionWeb;
use modelo\Requerimiento\Requerimiento;

include_once "modelo/ConexionWeb.php";
include_once "modelo/Conexion.php";
include_once "modelo/Requerimiento.php";

$nro_doc = 23106472769;
$oRequerimiento = new Requerimiento();
$oRequerimiento->__set('nro_doc', $nro_doc);
$retorno = $oRequerimiento->listarPendientes();

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
        <?php 
            var_dump($retorno);
        ?>
    </pre>
</body>
</html>