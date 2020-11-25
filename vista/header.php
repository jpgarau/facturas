
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="extras/css/bootstrap.min.css">
    <link rel="stylesheet" href="extras/css/alertify.css">
    <link rel="stylesheet" href="extras/css/themes/default.css">
    <link rel="stylesheet" href="extras/css/all.css">
    <link rel="stylesheet" href="extras/jquery-ui-1.12.1/jquery-ui.css">
    <link rel="stylesheet" href="css/estilos.css">
    <title>Mis Comprobantes</title>
    <script src='https://www.google.com/recaptcha/api.js'></script>
</head>
<?php
    $dir = !is_dir('modelo')?'../':'';
    include_once($dir.'modelo/validar.php');
    if(isset($_GET['registro']) || isset($_POST['registrar'])){
        include_once 'vista/registro.php';
    }
    if(!isset($_SESSION['usuario']) && !isset($_POST['registro']) && !isset($_POST['registrar'])){
        include_once($dir.'vista/login.php');
    }
?>