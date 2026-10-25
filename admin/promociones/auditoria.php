<?php
require '../../includes/sesiones.php';
checkRole('administrador');
require '../../includes/conexion.php';

$mensaje = filter_input(INPUT_GET, 'resultado', FILTER_UNSAFE_RAW);
$mensajes = [
    'aprobada' => ['clase' => 'success', 'texto' => 'La promoción fue aprobada correctamente.'],
    'denegada' => ['clase' => 'info', 'texto' => 'La promoción fue denegada correctamente.'],
];

$porPagina = 5;
$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
if ($pagina === false || $pagina === null || $pagina < 1) {
    $pagina = 1;
}

$stmtConteo = $conexion->prepare(
    "SELECT COUNT(*) AS total
     FROM PROMOCIONES
     WHERE estadoPromocion = 'pendiente'"
);
if (!$stmtConteo) {
    die('No se pudo preparar el conteo de promociones pendientes.');
}
$stmtConteo->execute();
$totalRegistros = (int) $stmtConteo->get_result()->fetch_assoc()['total'];
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$stmt = $conexion->prepare(
    "SELECT p.codPromocion, p.descripcionPromocion, p.descuentoPromocion,
            p.estadoPromocion, a.nombreAerolinea, a.codigoIATA
     FROM PROMOCIONES p
     INNER JOIN AEROLINEAS a ON a.codAerolinea = p.codAerolinea
     WHERE p.estadoPromocion = 'pendiente'
     ORDER BY p.codPromocion ASC
     LIMIT ? OFFSET ?"
);
if (!$stmt) {
    die('No se pudo preparar la consulta de auditoría.');
}
$stmt->bind_param('ii', $porPagina, $offset);
$stmt->execute();
$resultado = $stmt->get_result();
if (!$resultado) {
    die('No se pudieron obtener las promociones pendientes.');
}

$title = 'Auditar Promociones | ViajAir';
require '../../includes/header.php';
?>

<div class="row">
    <?php
    $active_page = 'auditoria';
    require '../sidebar_admin.php';
    ?>

    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="/tpi/admin/dashboard.php">Administración</a></li>
                <li class="breadcrumb-item active" aria-current="page">Auditar promociones</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h1 class="h5 mb-0">Auditoría de Promociones Pendientes</h1>
            </div>
            <div class="card-body p-4">
                <?php if (isset($mensajes[$mensaje])): ?>
                    <div class="alert alert-<?= $mensajes[$mensaje]['clase']; ?>"
                         role="status" aria-live="polite">
                        <?= $mensajes[$mensaje]['texto']; ?>
                    </div>
                <?php endif; ?>

                <p class="mb-4">
                    Revisá cada propuesta. Una vez resuelta, la promoción no puede volver a modificarse.
                </p>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover align-middle"
                           aria-labelledby="titulo-tabla-auditoria">
                        <caption id="titulo-tabla-auditoria" class="visually-hidden">
                            Promociones pendientes de resolución administrativa
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col">Promoción</th>
                                <th scope="col">Aerolínea</th>
                                <th scope="col">Descripción</th>
                                <th scope="col">Descuento</th>
                                <th scope="col">Estado</th>
                                <th scope="col" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado->num_rows > 0): ?>
                                <?php while ($promocion = $resultado->fetch_assoc()): ?>
                                    <?php $idPromocion = (int) $promocion['codPromocion']; ?>
                                    <tr>
                                        <th scope="row">#<?= $idPromocion; ?></th>
                                        <td>
                                            <?= htmlspecialchars($promocion['nombreAerolinea'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                                            <span class="badge bg-light text-dark border">
                                                IATA: <?= htmlspecialchars($promocion['codigoIATA'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($promocion['descripcionPromocion'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($promocion['descuentoPromocion'], ENT_QUOTES, 'UTF-8'); ?>%</td>
                                        <td>
                                            <span class="badge bg-warning text-dark"
                                                  aria-label="Estado: Pendiente">Pendiente</span>
                                        </td>
                                        <td class="text-center">
                                            <form action="/tpi/admin/promociones/procesar_auditoria.php" method="POST"
                                                  class="d-flex flex-wrap justify-content-center gap-1">
                                                <input type="hidden" name="id" value="<?= $idPromocion; ?>">
                                                <button type="submit" name="accion" value="aprobar"
                                                        class="btn btn-sm btn-success">
                                                    Aprobar
                                                    <span class="visually-hidden"> promoción número <?= $idPromocion; ?></span>
                                                </button>
                                                <button type="submit" name="accion" value="denegar"
                                                        class="btn btn-sm btn-danger">
                                                    Denegar
                                                    <span class="visually-hidden"> promoción número <?= $idPromocion; ?></span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        No hay promociones pendientes de auditoría.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPaginas > 1): ?>
                    <nav aria-label="Paginación de promociones pendientes">
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
                                            <?= $i; ?>
                                            <span class="visually-hidden">(página actual)</span>
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
