<?php

use controlador\ComprobantesC\ComprobantesC;

$dir = is_dir('modelo') ? '' : '../';

require_once $dir . 'modelo/validar.php';
require_once $dir . 'vista/header.php';
require_once $dir . 'controlador/ComprobantesC.php';

if (isset($_SESSION['usuario'])) {
?>
    <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#"><i style="font-family: 'Poppins', sans-serif; font-weight: bold;">Clientes</i></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a class="nav-link" href="#comprobantes">Mis Comprobantes</a></li>
            </ul>
            <div class="d-flex h-100 justify-content-center align-items-center mx-2">
                <p class="text-white p-0 m-0"><i class="fas fa-user"></i> <?php echo $_SESSION['usuario']; ?></p>
            </div>
            <a class="btn btn-warning my-2 my-sm-0" href="vista/logout.php"><i class="fas fa-door-open"></i> Salir</a>
        </div>
    </nav>

    <section id="comprobantes">
        <div class="container">
            <h2 class="text-center">Mis Comprobantes</h2>
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
<?php
    require_once 'vista/footer.php';
}
?>

</body>

</html>