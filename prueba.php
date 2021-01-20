<?php

use modelo\Requerimiento\Requerimiento;

require_once "modelo/Requerimiento.php";

$orequerimiento = new Requerimiento();

$orequerimiento->__set('fecha', '2020-01-18');
$orequerimiento->__set('nro_doc', 25851212);
$orequerimiento->__set('requerimiento', 'pruebaaaa');
$orequerimiento->__set('prioridad', null);
$orequerimiento->__set('estado', 0);

$retorno = $orequerimiento->agregar();
?>

<!DOCTYPE html>
<html lang="es">
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