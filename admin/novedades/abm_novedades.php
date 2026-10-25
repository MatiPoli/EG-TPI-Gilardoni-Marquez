<?php
require '../../includes/sesiones.php';
checkRole('administrador');
require '../../includes/conexion.php';

$porPagina = 5;
$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
if ($pagina === false || $pagina === null || $pagina < 1) {
    $pagina = 1;
}
$stmtConteo = $conexion->prepare('SELECT COUNT(*) AS total FROM NOVEDADES');
if (!$stmtConteo) {
    die('No se pudo preparar el conteo de novedades.');
}
$stmtConteo->execute();
$totalRegistros = (int) $stmtConteo->get_result()->fetch_assoc()['total'];
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$stmt = $conexion->prepare(
    'SELECT codNovedad, tituloNovedad, textoNovedad, fechaPublicacionNovedad, fechaExpiracionNovedad
     FROM NOVEDADES ORDER BY fechaPublicacionNovedad DESC LIMIT ? OFFSET ?'
);
if (!$stmt) {
    die('No se pudo preparar el listado de novedades.');
}
$stmt->bind_param('ii', $porPagina, $offset);
$stmt->execute();
$resultado = $stmt->get_result();
if (!$resultado) {
    die('No se pudieron obtener las novedades.');
}

$hoy = date('Y-m-d');
$title = 'Gestionar Novedades | ViajAir';
require '../../includes/header.php';
?>
<div class="row">
    <?php $active_page = 'novedades'; require '../sidebar_admin.php'; ?>
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="/tpi/admin/dashboard.php">Administración</a></li>
            <li class="breadcrumb-item active" aria-current="page">Gestión de novedades</li>
        </ol></nav>
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h1 class="h5 mb-0">Listado de novedades</h1>
                <a href="form_novedad.php" class="btn btn-light btn-sm fw-bold">Nueva novedad</a>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover align-middle" aria-labelledby="titulo-tabla-novedades">
                        <caption id="titulo-tabla-novedades" class="visually-hidden">Listado de novedades</caption>
                        <thead><tr>
                            <th scope="col">Código</th><th scope="col">Novedad</th><th scope="col">Publicación</th>
                            <th scope="col">Expiración</th><th scope="col">Estado</th><th scope="col" class="text-center">Acciones</th>
                        </tr></thead>
                        <tbody>
                            <?php if ($resultado->num_rows > 0): ?>
                                <?php while ($novedad = $resultado->fetch_assoc()): ?>
                                    <?php
                                    $idNovedad = (int) $novedad['codNovedad'];
                                    $vigente = $novedad['fechaPublicacionNovedad'] <= $hoy && $novedad['fechaExpiracionNovedad'] >= $hoy;
                                    $estado = $vigente ? ['clase' => 'bg-success', 'texto' => 'Vigente'] : ( $novedad['fechaExpiracionNovedad'] < $hoy ? ['clase' => 'bg-secondary', 'texto' => 'Expirada'] : ['clase' => 'bg-warning text-dark', 'texto' => 'Programada']);
                                    $texto = $novedad['tituloNovedad'] ?: $novedad['textoNovedad'];
                                    ?>
                                    <tr>
                                        <th scope="row">#<?= $idNovedad; ?></th>
                                        <td>
                                            <strong><?= htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></strong>
                                            <?php if ($novedad['tituloNovedad']): ?><span class="d-block small"><?= htmlspecialchars($novedad['textoNovedad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span><?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($novedad['fechaPublicacionNovedad'])); ?></td>
                                        <td><?= date('d/m/Y', strtotime($novedad['fechaExpiracionNovedad'])); ?></td>
                                        <td><span class="badge <?= $estado['clase']; ?>"><?= $estado['texto']; ?></span></td>
                                        <td class="text-center text-nowrap">
                                            <a href="form_novedad.php?id=<?= $idNovedad; ?>" class="btn btn-sm btn-warning">Editar <span class="visually-hidden">novedad <?= $idNovedad; ?></span></a>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalEliminarNovedad<?= $idNovedad; ?>">Eliminar <span class="visually-hidden">novedad <?= $idNovedad; ?></span></button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?><tr><td colspan="6" class="text-center">No hay novedades registradas.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($resultado->num_rows > 0): ?>
                    <?php $resultado->data_seek(0); while ($novedad = $resultado->fetch_assoc()): $idNovedad = (int) $novedad['codNovedad']; ?>
                        <div class="modal fade" id="modalEliminarNovedad<?= $idNovedad; ?>" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="modalEliminarNovedadLabel<?= $idNovedad; ?>" aria-hidden="true">
                            <div class="modal-dialog"><div class="modal-content">
                                <div class="modal-header bg-danger text-white"><h2 class="modal-title h5" id="modalEliminarNovedadLabel<?= $idNovedad; ?>">Confirmar eliminación</h2><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar modal"></button></div>
                                <div class="modal-body"><p>¿Deseás eliminar la novedad número <?= $idNovedad; ?>?</p></div>
                                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><a href="eliminar_novedad.php?id=<?= $idNovedad; ?>" class="btn btn-danger">Sí, eliminar <span class="visually-hidden">novedad <?= $idNovedad; ?></span></a></div>
                            </div></div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
                <?php if ($totalPaginas > 1): ?>
                    <nav aria-label="Paginación de novedades"><ul class="pagination justify-content-center flex-wrap mt-4">
                        <?php if ($pagina <= 1): ?><li class="page-item disabled"><span class="page-link" aria-disabled="true"><span aria-hidden="true">&laquo;</span><span class="visually-hidden">Página anterior deshabilitada</span></span></li>
                        <?php else: ?><li class="page-item"><a class="page-link" href="?pagina=<?= $pagina - 1; ?>"><span aria-hidden="true">&laquo;</span><span class="visually-hidden">Ir a la página anterior</span></a></li><?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <?php if ($pagina === $i): ?><li class="page-item active"><span class="page-link" aria-current="page"><?= $i; ?> <span class="visually-hidden">(página actual)</span></span></li>
                            <?php else: ?><li class="page-item"><a class="page-link" href="?pagina=<?= $i; ?>"><span class="visually-hidden">Ir a la página </span><?= $i; ?></a></li><?php endif; ?>
                        <?php endfor; ?>
                        <?php if ($pagina >= $totalPaginas): ?><li class="page-item disabled"><span class="page-link" aria-disabled="true"><span aria-hidden="true">&raquo;</span><span class="visually-hidden">Página siguiente deshabilitada</span></span></li>
                        <?php else: ?><li class="page-item"><a class="page-link" href="?pagina=<?= $pagina + 1; ?>"><span aria-hidden="true">&raquo;</span><span class="visually-hidden">Ir a la página siguiente</span></a></li><?php endif; ?>
                    </ul></nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require '../../includes/footer.php'; ?>
