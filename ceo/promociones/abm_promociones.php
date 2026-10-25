<?php
require '../../includes/sesiones.php';
checkRole('ceo');
require '../../includes/conexion.php';

$codAerolinea = (int) $_SESSION['codAerolinea'];
$porPagina = 10;
$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
if ($pagina === false || $pagina === null || $pagina < 1) {
    $pagina = 1;
}

$stmtConteo = $conexion->prepare('SELECT COUNT(*) AS total FROM PROMOCIONES WHERE codAerolinea = ?');
if (!$stmtConteo) {
    die('No se pudo preparar el conteo de promociones.');
}
$stmtConteo->bind_param('i', $codAerolinea);
$stmtConteo->execute();
$totalRegistros = (int) $stmtConteo->get_result()->fetch_assoc()['total'];
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$stmt = $conexion->prepare(
    'SELECT codPromocion, descripcionPromocion, descuentoPromocion, estadoPromocion
     FROM PROMOCIONES
     WHERE codAerolinea = ?
     ORDER BY codPromocion DESC
     LIMIT ? OFFSET ?'
);
if (!$stmt) {
    die('No se pudo preparar la consulta de promociones.');
}
$stmt->bind_param('iii', $codAerolinea, $porPagina, $offset);
$stmt->execute();
$resultado = $stmt->get_result();
if (!$resultado) {
    die('No se pudieron obtener las promociones.');
}

$estados = [
    'pendiente' => ['clase' => 'bg-warning text-dark', 'texto' => 'Pendiente'],
    'aprobada' => ['clase' => 'bg-success', 'texto' => 'Aprobada'],
    'denegada' => ['clase' => 'bg-danger', 'texto' => 'Denegada'],
];

$title = 'Gestionar Promociones | ViajAir';
require '../../includes/header.php';
?>

<div class="row">
    <?php
    $active_page = 'promociones';
    require '../sidebar_ceo.php';
    ?>

    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="/tpi/ceo/dashboard.php">Panel CEO</a></li>
                <li class="breadcrumb-item active" aria-current="page">Promociones</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <h1 class="h5 mb-0">Listado de Promociones</h1>
                <a href="form_promocion.php" class="btn btn-light btn-sm fw-bold">Nueva promoción</a>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover align-middle"
                           aria-labelledby="titulo-tabla-promociones">
                        <caption id="titulo-tabla-promociones" class="visually-hidden">Listado de promociones</caption>
                        <thead>
                            <tr>
                                <th scope="col">Código</th>
                                <th scope="col">Descripción</th>
                                <th scope="col">Descuento</th>
                                <th scope="col">Estado</th>
                                <th scope="col" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado->num_rows > 0): ?>
                                <?php while ($promocion = $resultado->fetch_assoc()): ?>
                                    <?php
                                    $idPromocion = (int) $promocion['codPromocion'];
                                    $estado = $estados[$promocion['estadoPromocion']] ?? [
                                        'clase' => 'bg-secondary',
                                        'texto' => 'Estado desconocido',
                                    ];
                                    ?>
                                    <tr>
                                        <th scope="row">#<?= $idPromocion; ?></th>
                                        <td><?= htmlspecialchars($promocion['descripcionPromocion'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($promocion['descuentoPromocion'], ENT_QUOTES, 'UTF-8'); ?>%</td>
                                        <td>
                                            <span class="badge <?= $estado['clase']; ?>"
                                                  aria-label="Estado: <?= $estado['texto']; ?>">
                                                <?= $estado['texto']; ?>
                                            </span>
                                        </td>
                                        <td class="text-center text-nowrap">
                                            <?php if ($promocion['estadoPromocion'] === 'pendiente'): ?>
                                                <a class="btn btn-sm btn-warning"
                                                   href="form_promocion.php?id=<?= $idPromocion; ?>">
                                                    Editar
                                                    <span class="visually-hidden"> promoción número <?= $idPromocion; ?></span>
                                                </a>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEliminarPromocion<?= $idPromocion; ?>">
                                                Eliminar
                                                <span class="visually-hidden"> promoción número <?= $idPromocion; ?></span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No hay promociones para mostrar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($resultado->num_rows > 0): ?>
                    <?php $resultado->data_seek(0); ?>
                    <?php while ($promocion = $resultado->fetch_assoc()): ?>
                        <?php $idPromocion = (int) $promocion['codPromocion']; ?>
                        <div class="modal fade" id="modalEliminarPromocion<?= $idPromocion; ?>" tabindex="-1"
                             role="dialog" aria-modal="true"
                             aria-labelledby="modalEliminarPromocionLabel<?= $idPromocion; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h2 class="modal-title h5 mb-0" id="modalEliminarPromocionLabel<?= $idPromocion; ?>">
                                            Confirmar eliminación de promoción
                                        </h2>
                                        <button type="button" class="btn-close btn-close-white"
                                                data-bs-dismiss="modal" aria-label="Cerrar modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>¿Estás seguro de que deseás eliminar la promoción número <?= $idPromocion; ?>?</p>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <a href="eliminar_promocion.php?id=<?= $idPromocion; ?>" class="btn btn-danger fw-bold">
                                            Sí, eliminar
                                            <span class="visually-hidden"> promoción número <?= $idPromocion; ?></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>

                <?php if ($totalPaginas > 1): ?>
                    <nav aria-label="Paginación de promociones">
                        <ul class="pagination justify-content-center flex-wrap mt-4">
                            <?php if ($pagina <= 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link" aria-disabled="true">
                                        <span aria-hidden="true">&laquo;</span>
                                        <span class="visually-hidden">Página anterior deshabilitada</span>
                                    </span>
                                </li>
                            <?php else: ?>
                                <li class="page-item">
                                    <a class="page-link" href="?pagina=<?= $pagina - 1; ?>">
                                        <span aria-hidden="true">&laquo;</span>
                                        <span class="visually-hidden">Ir a la página anterior</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <?php if ($pagina === $i): ?>
                                    <li class="page-item active">
                                        <span class="page-link" aria-current="page">
                                            <?= $i; ?> <span class="visually-hidden">(página actual)</span>
                                        </span>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?= $i; ?>">
                                            <span class="visually-hidden">Ir a la página </span><?= $i; ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <?php if ($pagina >= $totalPaginas): ?>
                                <li class="page-item disabled">
                                    <span class="page-link" aria-disabled="true">
                                        <span aria-hidden="true">&raquo;</span>
                                        <span class="visually-hidden">Página siguiente deshabilitada</span>
                                    </span>
                                </li>
                            <?php else: ?>
                                <li class="page-item">
                                    <a class="page-link" href="?pagina=<?= $pagina + 1; ?>">
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
