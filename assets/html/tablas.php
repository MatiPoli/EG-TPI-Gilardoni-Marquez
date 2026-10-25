<?php

require '../../includes/sesiones.php';
checkRole('asset');
require '../../includes/conexion.php';

$title = 'Tabla genérica | ViajAir';

$estados = [
    'confirmada' => ['clase' => 'bg-success', 'texto' => 'Confirmada'],
    'pendiente de pago' => ['clase' => 'bg-warning text-dark', 'texto' => 'Pendiente de pago'],
    'cancelada' => ['clase' => 'bg-danger', 'texto' => 'Cancelada'],
];

$porPagina = 5;
$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
if ($pagina === false || $pagina === null || $pagina < 1) {
    $pagina = 1;
}

// Añadí aquí los filtros GET que se apliquen también en las consultas de conteo y resultados.
$parametros_filtro = [];
$query_filtros = http_build_query($parametros_filtro);
$url_filtros = $query_filtros !== '' ? '&' . $query_filtros : '';

$resultadoConteo = $conexion->query('SELECT COUNT(*) AS total FROM RESERVAS');
if ($resultadoConteo === false) {
    die('No se pudo obtener el total de reservas.');
}
$total_registros = (int) $resultadoConteo->fetch_assoc()['total'];
$total_paginas = max(1, (int) ceil($total_registros / $porPagina));
$pagina = min($pagina, $total_paginas);
$offset = ($pagina - 1) * $porPagina;

// Adaptá esta consulta y los alias al contenido de la tabla que necesites mostrar.
$query = "SELECT r.codReserva, v.codVuelo, u.nombreUsuario, r.estadoReserva
          FROM RESERVAS r
          INNER JOIN VUELOS v ON r.codVuelo = v.codVuelo
          INNER JOIN USUARIOS u ON r.codUsuario = u.codUsuario
          ORDER BY r.codReserva DESC
          LIMIT ? OFFSET ?";
$stmt = $conexion->prepare($query);
if (!$stmt) {
    die('No se pudo preparar la consulta de reservas.');
}
$stmt->bind_param('ii', $porPagina, $offset);
$stmt->execute();
$result = $stmt->get_result();
if (!$result) {
    die('No se pudieron obtener las reservas.');
}

require '../../includes/header.php';
?>

<div class="container mb-5">
    <h1 id="titulo-tabla" class="mb-4">Reservas</h1>

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover align-middle" aria-labelledby="caption-tabla">
            <caption id="caption-tabla" class="caption-top text-center">Listado de reservas</caption>
            <thead>
                <tr>
                    <th scope="col">Reserva</th>
                    <th scope="col">Vuelo</th>
                    <th scope="col">Pasajero</th>
                    <th scope="col">Estado</th>
                    <th scope="col" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($reserva = $result->fetch_assoc()): ?>
                        <?php
                        $idReserva = (int) $reserva['codReserva'];
                        $estadoReserva = $reserva['estadoReserva'];
                        $estado = $estados[$estadoReserva] ?? [
                            'clase' => 'bg-secondary',
                            'texto' => 'Sin estado',
                        ];
                        ?>
                        <tr>
                            <th scope="row">#<?= $idReserva; ?></th>
                            <td>VA-<?= (int) $reserva['codVuelo']; ?></td>
                            <td><?= htmlspecialchars($reserva['nombreUsuario'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                            <td>
                                <span class="badge <?= htmlspecialchars($estado['clase'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= htmlspecialchars($estado['texto'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td class="text-center text-nowrap">
                                <a class="btn btn-sm btn-warning"
                                   href="form_reserva.php?id=<?= $idReserva; ?>">
                                    Editar
                                    <span class="visually-hidden">reserva número <?= $idReserva; ?></span>
                                </a>

                                <?php if ($estadoReserva === 'pendiente de pago'): ?>
                                    <button type="button" class="btn btn-sm btn-success"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalPagarReserva<?= $idReserva; ?>">
                                        Pagar
                                        <span class="visually-hidden">reserva número <?= $idReserva; ?></span>
                                    </button>
                                <?php endif; ?>

                                <button type="button" class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEliminarReserva<?= $idReserva; ?>">
                                    Eliminar
                                    <span class="visually-hidden">reserva número <?= $idReserva; ?></span>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No hay reservas para mostrar.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <?php if (!$result->data_seek(0)): ?>
            <?php die('No se pudo volver al inicio del resultado para mostrar las acciones.'); ?>
        <?php endif; ?>
        <?php while ($reserva = $result->fetch_assoc()): ?>
            <?php
            $idReserva = (int) $reserva['codReserva'];
            $estadoReserva = $reserva['estadoReserva'];
            ?>
            <?php if ($estadoReserva === 'pendiente de pago'): ?>
                <div class="modal fade" id="modalPagarReserva<?= $idReserva; ?>" tabindex="-1"
                     role="dialog" aria-modal="true"
                     aria-labelledby="modalPagarReservaLabel<?= $idReserva; ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-success text-white">
                                <h2 class="modal-title h5 mb-0" id="modalPagarReservaLabel<?= $idReserva; ?>">
                                    Confirmar pago de reserva
                                </h2>
                                <button type="button" class="btn-close btn-close-white"
                                        data-bs-dismiss="modal" aria-label="Cerrar modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <p>¿Estás seguro de que deseás pagar la reserva #<?= $idReserva; ?>?</p>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <a href="/tpi/pasajero/pagar_reserva.php?id=<?= $idReserva; ?>"
                                   class="btn btn-success fw-bold">
                                    Sí, pagar
                                    <span class="visually-hidden">reserva número <?= $idReserva; ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="modal fade" id="modalEliminarReserva<?= $idReserva; ?>" tabindex="-1"
                 role="dialog" aria-modal="true"
                 aria-labelledby="modalEliminarReservaLabel<?= $idReserva; ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-danger text-white">
                            <h2 class="modal-title h5 mb-0" id="modalEliminarReservaLabel<?= $idReserva; ?>">
                                Confirmar eliminación
                            </h2>
                            <button type="button" class="btn-close btn-close-white"
                                    data-bs-dismiss="modal" aria-label="Cerrar modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p>¿Estás seguro de que deseás eliminar la reserva #<?= $idReserva; ?>? Esta acción no se puede deshacer.</p>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <!-- En producción, validá permisos y token CSRF en el endpoint de borrado. -->
                            <form action="eliminar_reserva.php" method="post" class="d-inline">
                                <input type="hidden" name="id" value="<?= $idReserva; ?>">
                                <button type="submit" class="btn btn-danger">
                                    Sí, eliminar
                                    <span class="visually-hidden">reserva número <?= $idReserva; ?></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <?php if ($total_paginas > 1): ?>
        <nav aria-label="Paginación de reservas">
            <ul class="pagination justify-content-center">
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
                        <li class="page-item active" aria-current="page">
                            <span class="page-link">
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

<?php require '../../includes/footer.php'; ?>
