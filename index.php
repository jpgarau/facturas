<?php

use controlador\ClienteC\ClienteC;
use controlador\ComprobantesC\ComprobantesC;
use controlador\PresupuestosC\PresupuestosC;
use modelo\Cliente\Cliente;

$dir = is_dir('modelo') ? '' : '../';

require_once $dir . 'modelo/validar.php';
if(isset($_SESSION['userProfile'])){
    if($_SESSION['userProfile']['password_request']==1){
        header('Location: /facturas/vista/cambia_pass.php');
        die();
    }
}

if(!isset($_SESSION['usuario']) && !isset($_POST['registro']) && !isset($_POST['registrar'])){
    header('Location: /facturas/vista/login.php');
    die();
}
if(isset($_POST['button'])){
    require_once $dir . 'funciones/funciones.php';
    $email = 'desarrollos@agontech.com.ar';
    $nombre = $_SESSION['userProfile']['nombre'];
    $requerimiento = $_POST['button'];
    $asunto = html_entity_decode('Informaci&oacute;n de Producto - Agontech Clientes Web');
    $cuerpo = "<b>Estimado:</b> <br /><br />El cliente $nombre solicita m&aacute;s informaci&oacute;n sobre el producto: $requerimiento";
    if(enviarEmail($email, $nombre, $asunto, $cuerpo)){
        $_SESSION['msg_ok'] = "Se envió la solicitud de más información sobre $requerimiento. En breve recibira una respuesta. Gracias <b>Agontech SAS</b>";
    }else{
        $_SESSION['msg_error'] = "Error al enviar Email";
    }
    unset($_POST);
    header('Location: /facturas');
    exit;
}
require_once $dir . 'vista/header.php';
require_once $dir . 'controlador/ComprobantesC.php';
require_once $dir . 'controlador/PresupuestosC.php';
require_once $dir . 'controlador/ServicioC.php';
require_once $dir . 'controlador/ClienteC.php';

if (isset($_SESSION['usuario']) && ($_SESSION['userProfile']['password_request'])<1) {
?>
    <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#"><img src="/facturas/img/logo.png" alt="Logo Agontech"></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a class="nav-link" href="#servicios">Servicios</a></li>
                <li class="nav-item"><a class="nav-link" href="#comprobantes">Mis Comprobantes</a></li>
                <li class="nav-item"><a class="nav-link" href="#presupuestos">Mis Presupuestos</a></li>
                <!-- <li class="nav-item"><a class="nav-link" href="#ctacte">Mi Estado</a></li> -->
            </ul>
            <div class="d-flex h-100 justify-content-center align-items-center mx-2">
                <p class="text-white p-0 m-0"><i class="fas fa-user"></i> <?php echo $_SESSION['usuario']; ?></p>
            </div>
            <a class="btn btn-warning my-2 my-sm-0" href="vista/logout.php"><i class="fas fa-door-open"></i> Salir</a>
        </div>
    </nav>
    
    <section id="servicios">
        <div class="container">
            <h2 class="text-center titulo">Servicios</h2>
            <form action="" method="POST">
                <?php
                    if(isset($_SESSION['msg_ok'])){
                        echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading">Solicitud Enviada!</h4><button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button><p>'.$_SESSION['msg_ok'].'</p></div>';
                      unset($_SESSION['msg_ok']);
                    }
                    if(isset($_SESSION['msg_error'])){
                        echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading">Error</h4><button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button><p>'.$_SESSION['msg_error'].'</p></div>';
                      unset($_SESSION['msg_error']);
                    }
                ?>
            <div id="listaservicios" class="d-flex justify-content-center align-items-center">
                <?php
                    $num_doc = $_SESSION['userProfile']['num_doc'];
                    $clientec = new ClienteC();
                    $retorno = $clientec->listarServicios($num_doc);
                    $arrServicios = array();
                    if($retorno['encontrados'] > 0){
                        foreach ($retorno[0] as $servicioCliente) {
                            $arrServicios[] = $servicioCliente['idservicio'];
                        }
                    }

                    $servicioC = new ServicioC();
                    $rs = $servicioC->listarServicios();
                    if($rs['encontrados'] > 0){
                        $servicios = $rs[0];
                        foreach ($servicios as $servicio) {
                            if(in_array($servicio['idservicio'], $arrServicios)){
                                echo "<div class='servicio align-items-center'><p>".$servicio['descripcion']."</p><p><i class='fas fa-check-circle fa-2x activo'></i></p><p></p></div>";
                            }else{
                                echo "<div class='servicio align-items-center'><p>".$servicio['descripcion']."</p><p><i class='fas fa-check-circle fa-2x'></i></i></p><p>Contáctanos para más <button type='submit' name='button' class='btn btn-outline-light' value='".$servicio['descripcion']."'>Información</button></p></div>";
                            }
                        }
                    }
                ?>
            </div>
        </form>
        </div>
    </section>
    <!-- <hr> -->
    <section id="comprobantes">
        <div class="container">
            <h2 class="text-center titulo">Mis Comprobantes</h2>
            <div id="tbl_comprobantes" class="table-responsive">
                <table class="table table-light table-hover table-sm text-center">
                    <thead class="d-fixed">
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Nº de Comprobante</th>
                        <th>Importe</th>
                        <th>Descargar</th>
                        <th>Estado</th>
                    </thead>
                    <tbody>
                        <?php
                        $comprobanteC = new ComprobantesC();
                        $respuesta = $comprobanteC->listarComprobantes($_SESSION['userProfile']['num_doc']);
                        if ($respuesta['exito'] == true) {
                            if ($respuesta['encontrados'] > 0) {
                                $comprobantes = $respuesta[0];
                                foreach ($comprobantes as $comprobante) {
                                    echo    '</tr>'
                                        . '<td>'
                                        . (date("d/m/Y", strtotime($comprobante['fecha'])))
                                        . '</td>'
                                        . '<td>'
                                        . $comprobante['tipo_comp']
                                        . '</td>'
                                        . '<td>'
                                        . $comprobante['comprobante']
                                        . '</td>'
                                        . '<td>'
                                        . $comprobante['importe']
                                        . '</td>'
                                        . '<td>'
                                        . '<a href="/facturas/pdf/' . $comprobante["nombre_pdf"] . '" download><i class="far fa-file-pdf text-danger"></i></a>'
                                        . '</td>'
                                        . '<td>'
                                        . ($comprobante['estado_fact'] == 1 ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>')
                                        . '</td>'
                                        . '</tr>';
                                }
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <!-- <hr> -->
    <section id="presupuestos">
        <div class="container">
            <h2 class="text-center titulo">Mis Presupuestos</h2>
            <div id="tbl_presupuestos" class="table-responsive">
                <table class="table table-light table-hover table-sm text-center">
                    <thead class="d-fixed">
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Nº de Presupuesto</th>
                        <th>Importe</th>
                        <th>Descargar</th>
                        <th>Estado</th>
                    </thead>
                    <tbody>
                        <?php
                            $presupuestosC = new PresupuestosC();
                            $respuesta = $presupuestosC->listarPresupuestos($_SESSION['userProfile']['num_doc']);
                            if ($respuesta['exito'] == true) {
                                if ($respuesta['encontrados'] > 0) {
                                    $presupuestos = $respuesta[0];
                                    foreach ($presupuestos as $presupuesto) {
                                        echo    '</tr>'
                                            . '<td>'
                                            . (date("d/m/Y", strtotime($presupuesto['fecha'])))
                                            . '</td>'
                                            . '<td>'
                                            . $presupuesto['tipo_presu']
                                            . '</td>'
                                            . '<td>'
                                            . $presupuesto['presupuesto']
                                            . '</td>'
                                            . '<td>'
                                            . $presupuesto['importe']
                                            . '</td>'
                                            . '<td>'
                                            . '<a href="/facturas/pdf/' . $presupuesto["nombre_pdf"] . '" download><i class="far fa-file-pdf text-danger"></i></a>'
                                            . '</td>'
                                            . '<td>'
                                            . ($presupuesto['estado_presu'] == 1 ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>')
                                            . '</td>'
                                            . '</tr>';
                                    }
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <!-- <section id="ctacte">

    </section> -->
<?php
    require_once 'vista/footer.php';
}
?>

</body>

</html>