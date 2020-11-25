<?php

use modelo\Comprobante\Comprobante;

require_once 'modelo/Comprobante.php';

$oComprobante = new Comprobante();

$retorno = $oComprobante->realizarMantenimiento();

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
        <?php var_dump($retorno); ?>
    </pre>
</body>
</html>