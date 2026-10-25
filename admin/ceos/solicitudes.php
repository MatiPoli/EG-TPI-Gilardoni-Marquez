<?php
require '../../includes/sesiones.php';
checkRole('administrador');
require '../../includes/conexion.php';

$mensaje = filter_input(INPUT_GET, 'resultado', FILTER_UNSAFE_RAW);
$mensajes = [
    'aprobada' => ['clase' => 'success', 'texto' => 'La solicitud fue aprobada y vinculada a la aerolínea seleccionada.'],
    'rechazada' => ['clase' => 'info', 'texto' => 'La solicitud fue rechazada y la cuenta quedó suspendida.'],
    'error' => ['clase' => 'danger', 'texto' => 'Para aprobar una solicitud debés seleccionar una aerolínea.'],
];

$porPagina = 10;
$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
if ($pagina === false || $pagina === null || $pagina < 1) {
    $pagina = 1;
}

$stmtConteo = $conexion->prepare(
    "SELECT COUNT(*) AS total FROM USUARIOS
     WHERE tipoUsuario = 'ceo' AND estadoUsuario = 'pendiente'"
);
if (!$stmtConteo) {
    die('No se pudo preparar el conteo de solicitudes CEO.');
}
$stmtConteo->execute();
$totalRegistros = (int) $stmtConteo->get_result()->fetch_assoc()['total'];
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$stmt = $conexion->prepare(
    "SELECT codUsuario, nombreUsuario, emailUsuario, telefonoUsuario
     FROM USUARIOS
     WHERE tipoUsuario = 'ceo' AND estadoUsuario = 'pendiente'
     ORDER BY codUsuario ASC
     LIMIT ? OFFSET ?"
);
if (!$stmt) {
    die('No se pudo preparar el listado de solicitudes CEO.');
}
$stmt->bind_param('ii', $porPagina, $offset);
$stmt->execute();
$resultado = $stmt->get_result();
if (!$resultado) {
    die('No se pudieron obtener las solicitudes CEO.');
}

$aerolineas = $conexion->query(
    'SELECT codAerolinea, nombreAerolinea, codigoIATA FROM AEROLINEAS ORDER BY nombreAerolinea ASC'
);
if (!$aerolineas) {
    die('No se pudieron cargar las aerolíneas disponibles.');
}

$title = 'Solicitudes CEO | ViajAir';
require '../../includes/header.php';
?>

<div class="row">
    <?php
    $active_page = 'ceos';
    require '../sidebar_admin.php';
    ?>
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="/tpi/admin/dashboard.php">Administración</a></li>
                <li class="breadcrumb-item active" aria-current="page">Solicitudes CEO</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h1 class="h5 mb-0">Solicitudes de CEOs pendientes</h1>
            </div>
            <div class="card-body p-4">
                <?php if (isset($mensajes[$mensaje])): ?>
                    <div class="alert alert-<?= $mensajes[$mensaje]['clase']; ?>"
                         role="status" aria-live="polite">
                        <?= $mensajes[$mensaje]['texto']; ?>
                    </div>
                <?php endif; ?>

                <p class="mb-4">
                    Para aprobar una solicitud debés asignar una aerolínea. Una solicitud rechazada queda suspendida.
                </p>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover align-middle"
                           aria-labelledby="titulo-tabla-ceos">
                        <caption id="titulo-tabla-ceos" class="visually-hidden">
                            Solicitudes de CEOs pendientes de autorización
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col">Solicitante</th>
                                <th scope="col">Correo electrónico</th>
                                <th scope="col">Teléfono</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Aerolínea</th>
                                <th scope="col" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado->num_rows > 0): ?>
                                <?php while ($solicitud = $resultado->fetch_assoc()): ?>
                                    <?php $idUsuario = (int) $solicitud['codUsuario']; ?>
                                    <tr>
                                        <th scope="row"><?= htmlspecialchars($solicitud['nombreUsuario'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></th>
                                        <td><?= htmlspecialchars($solicitud['emailUsuario'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($solicitud['telefonoUsuario'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                        <td>
                                            <span class="badge bg-warning text-dark"
                                                  aria-label="Estado: Pendiente">Pendiente</span>
                                        </td>
                                        <td>
                                            <label for="aerolinea-<?= $idUsuario; ?>" class="visually-hidden">
                                                Aerolínea para <?= htmlspecialchars($solicitud['nombreUsuario'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                                            </label>
                                            <select id="aerolinea-<?= $idUsuario; ?>"
                                                    name="codAerolinea"
                                                    form="form-solicitud-<?= $idUsuario; ?>"
                                                    class="form-select form-select-sm">
                                                <option value="">Seleccionar aerolínea</option>
                                                <?php
                                                $aerolineas->data_seek(0);
                                                while ($aerolinea = $aerolineas->fetch_assoc()):
                                                ?>
                                                    <option value="<?= (int) $aerolinea['codAerolinea']; ?>">
                                                        <?= htmlspecialchars($aerolinea['nombreAerolinea'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                                                        (<?= htmlspecialchars($aerolinea['codigoIATA'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>)
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <form id="form-solicitud-<?= $idUsuario; ?>"
                                                  action="procesar_solicitud.php" method="POST"
                                                  class="d-flex flex-wrap justify-content-center gap-1">
                                                <input type="hidden" name="id" value="<?= $idUsuario; ?>">
                                                    <button type="submit" name="accion" value="aprobar"
                                                            class="btn btn-sm btn-success">
                                                        Aprobar
                                                        <span class="visually-hidden"> solicitud de <?= htmlspecialchars($solicitud['nombreUsuario'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span>
                                                    </button>
                                                    <button type="submit" name="accion" value="rechazar"
                                                            class="btn btn-sm btn-danger">
                                                        Rechazar
                                                        <span class="visually-hidden"> solicitud de <?= htmlspecialchars($solicitud['nombreUsuario'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span>
                                                    </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay solicitudes CEO pendientes.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPaginas > 1): ?>
                    <nav aria-label="Paginación de solicitudes CEO">
                        <ul class="pagination justify-content-center flex-wrap mt-4">
                            <?php if ($pagina <= 1): ?>
                                <li class="page-item disabled"><span class="page-link" aria-disabled="true"><span aria-hidden="true">&laquo;</span><span class="visually-hidden">Página anterior deshabilitada</span></span></li>
                            <?php else: ?>
                                <li class="page-item"><a class="page-link" href="?pagina=<?= $pagina - 1; ?>"><span aria-hidden="true">&laquo;</span><span class="visually-hidden">Ir a la página anterior</span></a></li>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <?php if ($pagina === $i): ?>
                                    <li class="page-item active"><span class="page-link" aria-current="page"><?= $i; ?> <span class="visually-hidden">(página actual)</span></span></li>
                                <?php else: ?>
                                    <li class="page-item"><a class="page-link" href="?pagina=<?= $i; ?>"><span class="visually-hidden">Ir a la página </span><?= $i; ?></a></li>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <?php if ($pagina >= $totalPaginas): ?>
                                <li class="page-item disabled"><span class="page-link" aria-disabled="true"><span aria-hidden="true">&raquo;</span><span class="visually-hidden">Página siguiente deshabilitada</span></span></li>
                            <?php else: ?>
                                <li class="page-item"><a class="page-link" href="?pagina=<?= $pagina + 1; ?>"><span aria-hidden="true">&raquo;</span><span class="visually-hidden">Ir a la página siguiente</span></a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>
