<?php
$dir = is_dir('modelo')?'':'../';
require $dir.'modelo/validar.php';
session_destroy();
unset($_SESSION);
header('Location: /facturas');