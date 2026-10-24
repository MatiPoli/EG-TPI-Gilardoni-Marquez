<?php

require '../../includes/sesiones.php';
// checkRole('administrador');

$title = 'Tabla genérica | ViajAir';

// Datos de ejemplo. En la página real, usá aquí las filas de la consulta guardadas en un array.
$reservas = [
    ['id' => 101, 'vuelo' => 'VA-101', 'pasajero' => 'Ana Pérez', 'estado' => 'confirmada'],
    ['id' => 102, 'vuelo' => 'VA-205', 'pasajero' => 'Luis Gómez', 'estado' => 'pendiente'],
    ['id' => 103, 'vuelo' => 'VA-310', 'pasajero' => 'Sofía Ruiz', 'estado' => 'cancelada'],
];

$estados = [
    'confirmada' => ['clase' => 'bg-success', 'texto' => 'Confirmada'],
    'pendiente' => ['clase' => 'bg-warning text-dark', 'texto' => 'Pendiente'],
    'cancelada' => ['clase' => 'bg-danger', 'texto' => 'Cancelada'],
];

// Valores de ejemplo. En producción, calculalos según la consulta y la página actual.
$total_paginas = 5;
$pagina = 2;

// Incluí aquí los filtros GET que la paginación debe conservar.
// Ejemplo: ['estado' => $_GET['estado'] ?? '', 'buscar' => $_GET['buscar'] ?? ''].
$parametros_filtro = [];
$query_filtros = http_build_query($parametros_filtro);
$url_filtros = $query_filtros !== '' ? '&' . $query_filtros : '';

require '../../includes/header.php';
?>

<div class="container mb-5">
    <h1 id="titulo-tabla" class="mb-4">Reservas</h1>

    <div class="table-responsive">
        <table class="table table-striped table-hover" aria-labelledby="titulo-tabla">
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
                <?php if (!empty($reservas)): ?>
                    <?php foreach ($reservas as $reserva): ?>
                        <?php
                        $estado = $estados[$reserva['estado']] ?? [
                            'clase' => 'bg-secondary',
                            'texto' => 'Sin estado',
                        ];
                        ?>
                        <tr>
                            <th scope="row">#<?= (int) $reserva['id']; ?></th>
                            <td><?= htmlspecialchars($reserva['vuelo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($reserva['pasajero'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                            <td>
                                <span class="badge <?= htmlspecialchars($estado['clase'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= htmlspecialchars($estado['texto'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td class="text-center text-nowrap">
                                <a class="btn btn-sm btn-warning"
                                   href="form_reserva.php?id=<?= (int) $reserva['id']; ?>">
                                    Editar
                                    <span class="visually-hidden">reserva número <?= (int) $reserva['id']; ?></span>
                                </a>

                                <?php if ($reserva['estado'] === 'pendiente'): ?>
                                    <button type="button" class="btn btn-sm btn-success"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalPagarReserva<?= (int) $reserva['id']; ?>">
                                        Pagar
                                        <span class="visually-hidden">reserva número <?= (int) $reserva['id']; ?></span>
                                    </button>
                                <?php endif; ?>

                                <button type="button" class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEliminarReserva<?= (int) $reserva['id']; ?>">
                                    Eliminar
                                    <span class="visually-hidden">reserva número <?= (int) $reserva['id']; ?></span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No hay reservas para mostrar.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php foreach ($reservas as $reserva): ?>
        <?php if ($reserva['estado'] === 'pendiente'): ?>
            <div class="modal fade" id="modalPagarReserva<?= (int) $reserva['id']; ?>" tabindex="-1"
                 role="dialog" aria-modal="true"
                 aria-labelledby="modalPagarReservaLabel<?= (int) $reserva['id']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-success text-white">
                            <h2 class="modal-title h5 mb-0" id="modalPagarReservaLabel<?= (int) $reserva['id']; ?>">
                                Confirmar pago de reserva
                            </h2>
                            <button type="button" class="btn-close btn-close-white"
                                    data-bs-dismiss="modal" aria-label="Cerrar modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p>¿Estás seguro de que deseás pagar la reserva #<?= (int) $reserva['id']; ?>?</p>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <a href="/tpi/pasajero/pagar_reserva.php?id=<?= (int) $reserva['id']; ?>"
                               class="btn btn-success fw-bold">
                                Sí, pagar
                                <span class="visually-hidden">reserva número <?= (int) $reserva['id']; ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="modal fade" id="modalEliminarReserva<?= (int) $reserva['id']; ?>" tabindex="-1"
             role="dialog" aria-modal="true"
             aria-labelledby="modalEliminarReservaLabel<?= (int) $reserva['id']; ?>" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h2 class="modal-title h5 mb-0" id="modalEliminarReservaLabel<?= (int) $reserva['id']; ?>">
                            Confirmar eliminación
                        </h2>
                        <button type="button" class="btn-close btn-close-white"
                                data-bs-dismiss="modal" aria-label="Cerrar modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p>¿Estás seguro de que deseás eliminar la reserva #<?= (int) $reserva['id']; ?>? Esta acción no se puede deshacer.</p>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <!-- En producción, validá permisos y token CSRF en el endpoint de borrado. -->
                        <form action="eliminar_reserva.php" method="post" class="d-inline">
                            <input type="hidden" name="id" value="<?= (int) $reserva['id']; ?>">
                            <button type="submit" class="btn btn-danger">
                                Sí, eliminar
                                <span class="visually-hidden">reserva número <?= (int) $reserva['id']; ?></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

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
