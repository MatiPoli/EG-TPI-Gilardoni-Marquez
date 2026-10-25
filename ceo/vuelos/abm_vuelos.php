<?php
require '../../includes/sesiones.php';
checkRole('ceo');
require '../../includes/conexion.php';


$por_pagina = 5;
$codAerolinea = (int) $_SESSION['codAerolinea'];
$origen = isset($_GET['origen']) && is_string($_GET['origen']) ? trim($_GET['origen']) : '';
$destino = isset($_GET['destino']) && is_string($_GET['destino']) ? trim($_GET['destino']) : '';
$fecha = isset($_GET['fecha']) && is_string($_GET['fecha']) ? trim($_GET['fecha']) : '';
$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
if ($pagina === false || $pagina === null || $pagina < 1) {
    $pagina = 1;
}

$condiciones = ['codAerolinea = ?'];
$parametros = [$codAerolinea];
$tipos = 'i';

if ($origen !== '') {
    $condiciones[] = 'origenVuelo LIKE ?';
    $parametros[] = '%' . $origen . '%';
    $tipos .= 's';
}
if ($destino !== '') {
    $condiciones[] = 'destinoVuelo LIKE ?';
    $parametros[] = '%' . $destino . '%';
    $tipos .= 's';
}
if ($fecha !== '') {
    $condiciones[] = 'fechaSalidaVuelo = ?';
    $parametros[] = $fecha;
    $tipos .= 's';
}

$whereSql = 'WHERE ' . implode(' AND ', $condiciones);

$queryConteo = "SELECT COUNT(*) AS total FROM VUELOS $whereSql";
$stmtConteo = $conexion->prepare($queryConteo);
if (!$stmtConteo) {
    die('No se pudo preparar el conteo de vuelos.');
}
$stmtConteo->bind_param($tipos, ...$parametros);
$stmtConteo->execute();
$resultadoConteo = $stmtConteo->get_result();
if (!$resultadoConteo) {
    die('No se pudo obtener el total de vuelos.');
}

$total_registros = (int) $resultadoConteo->fetch_assoc()['total'];
$total_paginas = max(1, (int) ceil($total_registros / $por_pagina));
$pagina = min($pagina, $total_paginas);
$offset = ($pagina - 1) * $por_pagina;

$filtros = [
    'origen' => $origen,
    'destino' => $destino,
    'fecha' => $fecha,
];
$url_filtros = http_build_query(array_filter($filtros, static function ($valor) {
    return $valor !== '';
}));
$url_filtros = $url_filtros !== '' ? '&' . $url_filtros : '';

$query = "SELECT * FROM VUELOS
          $whereSql
          ORDER BY codVuelo DESC
          LIMIT ? OFFSET ?";
$stmt = $conexion->prepare($query);
if (!$stmt) {
    die('No se pudo preparar la consulta de vuelos.');
}
$parametrosListado = $parametros;
$parametrosListado[] = $por_pagina;
$parametrosListado[] = $offset;
$stmt->bind_param($tipos . 'ii', ...$parametrosListado);
$stmt->execute();
$result = $stmt->get_result();
if (!$result) {
    die('No se pudieron obtener los vuelos.');
}

$title = "Gestionar Vuelos | ViajAir";
require '../../includes/header.php';
?>

<div class="row">
    <?php 
    $active_page = 'vuelos';
    require '../sidebar_ceo.php'; 
    ?>
    
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="/tpi/ceo/dashboard.php">Panel CEO</a></li>
                <li class="breadcrumb-item active" aria-current="page">Gestión de Vuelos</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <h1 class="h5 mb-0">Listado de Vuelos</h1>
                <a href="form_vuelo.php" class="btn btn-light btn-sm fw-bold">Nuevo Vuelo</a>
            </div>
            <div class="card-body p-4">
                <form action="abm_vuelos.php" method="GET" class="mb-4">
                    <fieldset class="row g-3 align-items-end border rounded p-3">
                        <legend class="h6 fw-bold text-dark">Buscar vuelos</legend>
                        <div class="col-12 col-md-4">
                            <label for="origen" class="form-label">Origen</label>
                            <input type="text" id="origen" name="origen" class="form-control"
                                   value="<?= htmlspecialchars($origen, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                                   maxlength="50">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="destino" class="form-label">Destino</label>
                            <input type="text" id="destino" name="destino" class="form-control"
                                   value="<?= htmlspecialchars($destino, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                                   maxlength="50">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="fecha" class="form-label">Fecha de salida</label>
                            <input type="date" id="fecha" name="fecha" class="form-control"
                                   value="<?= htmlspecialchars($fecha, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Buscar vuelos</button>
                            <a href="abm_vuelos.php" class="btn btn-outline-secondary">Limpiar filtros</a>
                        </div>
                    </fieldset>
                </form>
                <div class="table-responsive">
                    
                    <table class="table table-striped table-bordered table-hover align-middle" aria-labelledby="titulo-tabla">
                        <caption id="titulo-tabla" class="caption-top text-center" hidden>Listado de vuelos</caption>
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Origen</th>
                                <th scope="col">Destino</th>
                                <th scope="col">Fecha de Salida</th>
                                <th scope="col">Hora de Salida</th>
                                <th scope="col">Precio</th>
                                <th scope="col">Asientos Disponibles</th>
                                <th scope="col" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($vuelo = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= (int) $vuelo['codVuelo']; ?></td>
                                        <td><?= htmlspecialchars($vuelo['origenVuelo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($vuelo['destinoVuelo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                        <td><?= date('d/m/Y', strtotime($vuelo['fechaSalidaVuelo'])); ?></td>
                                        <td><?= substr($vuelo['horaSalidaVuelo'], 0, 5) . ' hs' ?></td>
                                        <td>$<?= number_format($vuelo['precioVuelo'], 2, ',', '.'); ?></td>
                                        <td><?= htmlspecialchars($vuelo['asientosDisponibles'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                        <td class="text-center">
                                            <?php
                                            $fechaHoraSalida = strtotime(
                                                $vuelo['fechaSalidaVuelo'] . ' ' . $vuelo['horaSalidaVuelo']
                                            );
                                            $vueloNoSalio = $fechaHoraSalida !== false && $fechaHoraSalida > time();
                                            if ($vueloNoSalio):
                                            ?>
                                                <div class="d-flex flex-wrap justify-content-center gap-1">
                                                    <a class="btn btn-sm btn-warning"
                                                       href="form_vuelo.php?id=<?= (int) $vuelo['codVuelo']; ?>">
                                                        Editar
                                                        <span class="visually-hidden">vuelo número <?= (int) $vuelo['codVuelo']; ?></span>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalEliminarVuelo<?= (int) $vuelo['codVuelo']; ?>">
                                                        Eliminar
                                                        <span class="visually-hidden">vuelo número <?= (int) $vuelo['codVuelo']; ?></span>
                                                    </button>
                                                </div>
                                            <?php else:?>
                                                <span>El vuelo ya salió.</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No hay vuelos para mostrar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($result->num_rows > 0): ?>
                    <?php if (!$result->data_seek(0)): ?>
                        <?php die('No se pudo volver al inicio del resultado para mostrar las confirmaciones.'); ?>
                    <?php endif; ?>
                    <?php while ($vuelo = $result->fetch_assoc()): ?>
                        <?php
                        $idVuelo = (int) $vuelo['codVuelo'];
                        $fechaHoraSalida = strtotime(
                            $vuelo['fechaSalidaVuelo'] . ' ' . $vuelo['horaSalidaVuelo']
                        );
                        $vueloNoSalio = $fechaHoraSalida !== false && $fechaHoraSalida > time();
                        ?>
                        <?php if ($vueloNoSalio): ?>
                            <div class="modal fade" id="modalEliminarVuelo<?= $idVuelo; ?>" tabindex="-1"
                                 role="dialog" aria-modal="true"
                                 aria-labelledby="modalEliminarVueloLabel<?= $idVuelo; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-danger text-white">
                                            <h2 class="modal-title h5 mb-0" id="modalEliminarVueloLabel<?= $idVuelo; ?>">
                                                Confirmar eliminación de vuelo
                                            </h2>
                                            <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal" aria-label="Cerrar modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <p>¿Estás seguro de que deseás eliminar el vuelo #<?= $idVuelo; ?>?</p>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <a href="eliminar_vuelo.php?id=<?= $idVuelo; ?>" class="btn btn-danger fw-bold">
                                                Sí, eliminar
                                                <span class="visually-hidden">vuelo número <?= $idVuelo; ?></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endwhile; ?>
                <?php endif; ?>
                <?php if ($total_paginas > 1): ?>
        <nav aria-label="Paginación de vuelos">
            <ul class="pagination justify-content-center flex-wrap">
                <?php if ($pagina <= 1): ?>
                    <li class="page-item disabled">
                        <span class="page-link" aria-disabled="true">
                            <span aria-hidden="true">&laquo;</span>
                            <span class="visually-hidden">Página anterior (deshabilitada)</span>
                        </span>
                    </li>
                <?php else: ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?= $pagina - 1; ?><?= htmlspecialchars($url_filtros, ENT_QUOTES, 'UTF-8'); ?>">
                            <span aria-hidden="true">&laquo;</span>
                            <span class="visually-hidden">Ir a la página anterior</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <?php if ($pagina === $i): ?>
                        <li class="page-item active">
                            <span class="page-link" aria-current="page">
                                <?= $i; ?>
                                <span class="visually-hidden">(página actual)</span>
                            </span>
                        </li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link" href="?pagina=<?= $i; ?><?= htmlspecialchars($url_filtros, ENT_QUOTES, 'UTF-8'); ?>">
                                <span class="visually-hidden">Ir a la página </span><?= $i; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagina >= $total_paginas): ?>
                    <li class="page-item disabled">
                        <span class="page-link" aria-disabled="true">
                            <span aria-hidden="true">&raquo;</span>
                            <span class="visually-hidden">Página siguiente (deshabilitada)</span>
                        </span>
                    </li>
                <?php else: ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?= $pagina + 1; ?><?= htmlspecialchars($url_filtros, ENT_QUOTES, 'UTF-8'); ?>">
                            <span aria-hidden="true">&raquo;</span>
                            <span class="visually-hidden">Ir a la página siguiente</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>