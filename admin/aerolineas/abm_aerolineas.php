<?php
require '../../includes/sesiones.php';
checkRole('administrador');
require '../../includes/conexion.php';

$porPagina = 5;
$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
if ($pagina === false || $pagina === null || $pagina < 1) {
    $pagina = 1;
}

$filtroPais = filter_input(INPUT_GET, 'codPais', FILTER_UNSAFE_RAW);
$filtroPais = is_string($filtroPais) ? strtoupper(trim($filtroPais)) : '';
if (!preg_match('/^[A-Z]{2,3}$/', $filtroPais)) {
    $filtroPais = '';
}

$filtros = [
    'codPais' => $filtroPais,
];
$urlFiltros = http_build_query(array_filter($filtros, static function ($valor) {
    return $valor !== '';
}));
$urlFiltros = $urlFiltros !== '' ? '&' . $urlFiltros : '';

$wherePais = $filtroPais !== '' ? ' WHERE codPais = ?' : '';
$stmtConteo = $conexion->prepare('SELECT COUNT(*) AS total FROM AEROLINEAS' . $wherePais);
if (!$stmtConteo) {
    die('No se pudo preparar el conteo de aerolíneas.');
}
if ($filtroPais !== '') {
    $stmtConteo->bind_param('s', $filtroPais);
}
$stmtConteo->execute();
$totalRegistros = (int) $stmtConteo->get_result()->fetch_assoc()['total'];
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$stmt = $conexion->prepare(
    'SELECT codAerolinea, nombreAerolinea, codigoIATA, descripcionAerolinea, codPais
     FROM AEROLINEAS' . $wherePais . ' ORDER BY codAerolinea DESC LIMIT ? OFFSET ?'
);
if (!$stmt) {
    die('No se pudo preparar el listado de aerolíneas.');
}
if ($filtroPais !== '') {
    $stmt->bind_param('sii', $filtroPais, $porPagina, $offset);
} else {
    $stmt->bind_param('ii', $porPagina, $offset);
}
$stmt->execute();
$resultado = $stmt->get_result();
if (!$resultado) {
    die('No se pudieron obtener las aerolíneas.');
}

$title = 'Gestionar Aerolíneas | ViajAir';
require '../../includes/header.php';
?>

<div class="row">
    <?php
    $active_page = 'aerolineas';
    require '../sidebar_admin.php';
    ?>
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="/tpi/admin/dashboard.php">Administración</a></li>
                <li class="breadcrumb-item active" aria-current="page">Gestión de Aerolíneas</li>
            </ol>
        </nav>
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <h1 class="h5 mb-0">Listado de Aerolíneas</h1>
                <a href="form_aerolinea.php" class="btn btn-light btn-sm fw-bold">Nueva aerolínea</a>
            </div>
            <div class="card-body p-4">
                <form action="abm_aerolineas.php" method="GET" class="mb-4">
                    <fieldset class="row g-3 align-items-end border rounded p-3">
                        <legend class="h6 fw-bold text-dark">Buscar aerolíneas</legend>
                        <div class="col-12 col-md-6 col-lg-4">
                            <label for="codPais" class="form-label">Código de país</label>
                            <input type="text" id="codPais" name="codPais" class="form-control"
                                   value="<?= htmlspecialchars($filtroPais, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                                   maxlength="3" pattern="[A-Za-z]{2,3}"
                                   aria-describedby="codPais-ayuda">
                            <div id="codPais-ayuda" class="form-text">Ingresá el código de 2 o 3 letras.</div>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Buscar aerolíneas</button>
                            <a href="abm_aerolineas.php" class="btn btn-outline-secondary">Limpiar filtros</a>
                        </div>
                    </fieldset>
                </form>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover align-middle"
                           aria-labelledby="titulo-tabla-aerolineas">
                        <caption id="titulo-tabla-aerolineas" class="visually-hidden">Listado de aerolíneas</caption>
                        <thead>
                            <tr>
                                <th scope="col">Código</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Código IATA</th>
                                <th scope="col">País</th>
                                <th scope="col" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado->num_rows > 0): ?>
                                <?php while ($aerolinea = $resultado->fetch_assoc()): ?>
                                    <?php $idAerolinea = (int) $aerolinea['codAerolinea']; ?>
                                    <tr>
                                        <th scope="row">#<?= $idAerolinea; ?></th>
                                        <td><?= htmlspecialchars($aerolinea['nombreAerolinea'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($aerolinea['codigoIATA'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($aerolinea['codPais'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                        <td class="text-center text-nowrap">
                                            <a href="form_aerolinea.php?id=<?= $idAerolinea; ?>" class="btn btn-sm btn-warning">
                                                Editar <span class="visually-hidden">aerolínea <?= $idAerolinea; ?></span>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEliminarAerolinea<?= $idAerolinea; ?>">
                                                Eliminar <span class="visually-hidden">aerolínea <?= $idAerolinea; ?></span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center">No hay aerolíneas registradas.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($resultado->num_rows > 0): ?>
                    <?php $resultado->data_seek(0); ?>
                    <?php while ($aerolinea = $resultado->fetch_assoc()): ?>
                        <?php $idAerolinea = (int) $aerolinea['codAerolinea']; ?>
                        <div class="modal fade" id="modalEliminarAerolinea<?= $idAerolinea; ?>" tabindex="-1"
                             role="dialog" aria-modal="true"
                             aria-labelledby="modalEliminarAerolineaLabel<?= $idAerolinea; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h2 class="modal-title h5" id="modalEliminarAerolineaLabel<?= $idAerolinea; ?>">Confirmar eliminación</h2>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>¿Deseás eliminar la aerolínea <?= htmlspecialchars($aerolinea['nombreAerolinea'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <a href="eliminar_aerolinea.php?id=<?= $idAerolinea; ?>" class="btn btn-danger">
                                            Sí, eliminar <span class="visually-hidden">aerolínea <?= $idAerolinea; ?></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>

                <?php if ($totalPaginas > 1): ?>
                    <nav aria-label="Paginación de aerolíneas">
                        <ul class="pagination justify-content-center flex-wrap mt-4">
                            <?php if ($pagina <= 1): ?>
                                <li class="page-item disabled"><span class="page-link" aria-disabled="true"><span aria-hidden="true">&laquo;</span><span class="visually-hidden">Página anterior deshabilitada</span></span></li>
                            <?php else: ?>
                                <li class="page-item"><a class="page-link" href="?pagina=<?= $pagina - 1; ?><?= htmlspecialchars($urlFiltros, ENT_QUOTES, 'UTF-8'); ?>"><span aria-hidden="true">&laquo;</span><span class="visually-hidden">Ir a la página anterior</span></a></li>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <?php if ($pagina === $i): ?>
                                    <li class="page-item active"><span class="page-link" aria-current="page"><?= $i; ?> <span class="visually-hidden">(página actual)</span></span></li>
                                <?php else: ?>
                                    <li class="page-item"><a class="page-link" href="?pagina=<?= $i; ?><?= htmlspecialchars($urlFiltros, ENT_QUOTES, 'UTF-8'); ?>"><span class="visually-hidden">Ir a la página </span><?= $i; ?></a></li>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <?php if ($pagina >= $totalPaginas): ?>
                                <li class="page-item disabled"><span class="page-link" aria-disabled="true"><span aria-hidden="true">&raquo;</span><span class="visually-hidden">Página siguiente deshabilitada</span></span></li>
                            <?php else: ?>
                                <li class="page-item"><a class="page-link" href="?pagina=<?= $pagina + 1; ?><?= htmlspecialchars($urlFiltros, ENT_QUOTES, 'UTF-8'); ?>"><span aria-hidden="true">&raquo;</span><span class="visually-hidden">Ir a la página siguiente</span></a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require '../../includes/footer.php'; ?>
