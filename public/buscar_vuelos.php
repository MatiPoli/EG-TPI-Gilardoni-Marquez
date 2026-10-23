<?php
require '../includes/sesiones.php';
require '../includes/conexion.php';

$origen = isset($_GET['origen']) ? trim($_GET['origen']) : '';
$destino = isset($_GET['destino']) ? trim($_GET['destino']) : '';
$fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : '';
$aerolinea = isset($_GET['aerolinea']) && is_numeric($_GET['aerolinea']) ? (int)$_GET['aerolinea'] : 0;

$res_aerolineas = $conexion->query("SELECT codAerolinea, nombreAerolinea FROM AEROLINEAS ORDER BY nombreAerolinea ASC");

$limite = 5;
$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) {
    $pagina = 1;
}
$offset = ($pagina - 1) * $limite;

$condiciones = ["v.asientosDisponibles > 0", "v.fechaSalidaVuelo >= CURDATE()"];
$params = [];
$tipos = "";

if (!empty($origen)) {
    $condiciones[] = "v.origenVuelo LIKE ?";
    $params[] = "%" . $origen . "%";
    $tipos .= "s";
}
if (!empty($destino)) {
    $condiciones[] = "v.destinoVuelo LIKE ?";
    $params[] = "%" . $destino . "%";
    $tipos .= "s";
}
if (!empty($fecha)) {
    $condiciones[] = "v.fechaSalidaVuelo = ?";
    $params[] = $fecha;
    $tipos .= "s";
}
if ($aerolinea > 0) {
    $condiciones[] = "v.codAerolinea = ?";
    $params[] = $aerolinea;
    $tipos .= "i";
}

$where_sql = "WHERE " . implode(" AND ", $condiciones);

$sql_total = "SELECT COUNT(*) AS total FROM VUELOS v $where_sql";
$stmt_total = $conexion->prepare($sql_total);
if (!empty($tipos)) {
    $stmt_total->bind_param($tipos, ...$params);
}
$stmt_total->execute();
$res_total = $stmt_total->get_result();
$row_total = $res_total->fetch_assoc();
$total_registros = $row_total['total'];
$total_paginas = ceil($total_registros / $limite);

$sql = "SELECT v.*, a.nombreAerolinea, p.descuentoPromocion, p.descripcionPromocion
        FROM VUELOS v
        INNER JOIN AEROLINEAS a ON v.codAerolinea = a.codAerolinea
        LEFT JOIN PROMOCIONES p ON a.codAerolinea = p.codAerolinea AND p.estadoPromocion = 'aprobada'
        $where_sql
        ORDER BY v.fechaSalidaVuelo ASC, v.horaSalidaVuelo ASC
        LIMIT $limite OFFSET $offset";

$stmt = $conexion->prepare($sql);
if (!empty($tipos)) {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$title = "Buscar Vuelos | ViajAir";
require '../includes/header.php';
?>

<div class="container mb-5">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Buscar Vuelos</li>
        </ol>
    </nav>

    <h1 class="mb-4 text-center text-black">Catálogo de Vuelos</h1>
    <?php if (isset($_GET['error_reserva']) && $_GET['error_reserva'] === 'transaccion'): ?>
        <div class="alert alert-danger alert-dismissible fade show text-center mb-4" role="alert">
            <strong>Ocurrió un error inesperado:</strong> No se pudo procesar la solicitud con el servidor. Por favor, intentá nuevamente en unos instantes.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar notificación"></button>
        </div>
    <?php endif; ?>
    <div class="card shadow-sm border-0 bg-secondary mb-4">
        <div class="card-body p-4">
            <h2 class="h5 card-title mb-3 text-center text-primary fw-bold">Parámetros de búsqueda</h2>
            <form action="/tpi/public/buscar_vuelos.php" method="GET" class="row g-3 align-items-end">
                
                <div class="col-md-3">
                    <label for="origen" class="form-label fw-semibold text-dark">Origen</label>
                    <input type="text" id="origen" name="origen" class="form-control" 
                           value="<?= htmlspecialchars($origen); ?>" 
                           placeholder="Ciudad de Origen">
                </div>

                <div class="col-md-3">
                    <label for="destino" class="form-label fw-semibold text-dark">Destino</label>
                    <input type="text" id="destino" name="destino" class="form-control" 
                           value="<?= htmlspecialchars($destino); ?>" 
                           placeholder="Ciudad de Destino">
                </div>

                <div class="col-md-2">
                    <label for="fecha" class="form-label fw-semibold text-dark">Fecha de salida</label>
                    <input type="date" id="fecha" name="fecha" class="form-control" 
                           value="<?= htmlspecialchars($fecha); ?>">
                </div>

                <div class="col-md-2">
                    <label for="aerolinea" class="form-label fw-semibold text-dark">Aerolínea</label>
                    <select id="aerolinea" name="aerolinea" class="form-select">
                        <option value="0">Todas</option>
                        <?php if ($res_aerolineas && $res_aerolineas->num_rows > 0): ?>
                            <?php while ($a = $res_aerolineas->fetch_assoc()): ?>
                                <option value="<?= $a['codAerolinea']; ?>" <?= ($aerolinea == $a['codAerolinea']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($a['nombreAerolinea']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary fw-bold">Buscar vuelos</button>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h6 mb-0 text-muted">
            Vuelos encontrados: <strong><?= $total_registros; ?></strong>
        </h2>
        <small class="text-muted">Precios en Pesos Argentinos ($)</small>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($vuelo = $result->fetch_assoc()): 
            $precioOriginal = (float)$vuelo['precioVuelo'];
            $descuento = (float)($vuelo['descuentoPromocion'] ?? 0);
            $tienePromo = $descuento > 0;
            $precioFinal = $tienePromo ? $precioOriginal * (1 - ($descuento / 100)) : $precioOriginal;
            $maxPasajes = min(10, (int)$vuelo['asientosDisponibles']);
        ?>
            <article class="card mb-3 shadow-sm border-0 bg-secondary">
                <div class="card-body p-4">
                    <div class="row align-items-center gy-3">
                        
                        <div class="col-12 col-md-6">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($vuelo['nombreAerolinea']); ?></span>
                                <?php if ($tienePromo): ?>
                                    <span class="badge bg-success" aria-label="Descuento aplicado: <?= $descuento; ?> por ciento">
                                        <?= $descuento; ?>% OFF
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h3 class="h4 mb-2 fw-bold text-dark">
                                <span><?= htmlspecialchars($vuelo['origenVuelo']); ?></span>
                                <span aria-hidden="true"> &rarr; </span>
                                <span class="visually-hidden"> hacia </span>
                                <span><?= htmlspecialchars($vuelo['destinoVuelo']); ?></span>
                            </h3>

                            <p class="text-muted mb-2">
                                <strong>Fecha de salida:</strong> <?= date('d/m/Y', strtotime($vuelo['fechaSalidaVuelo'])); ?> 
                                a las <strong><?= substr($vuelo['horaSalidaVuelo'], 0, 5); ?> hs (GMT-3)</strong>
                            </p>

                            <p class="mb-0">
                                <span class="badge bg-light text-dark border">
                                    Asientos disponibles: <strong><?= $vuelo['asientosDisponibles']; ?></strong>
                                </span>
                            </p>
                        </div>

                        <div class="col-12 col-md-3 text-center">
                            <?php if ($tienePromo): ?>
                                <span class="d-block text-muted small">
                                    <span class="visually-hidden">Precio anterior sin descuento: </span>
                                    <del>$ <?= number_format($precioOriginal, 2, ',', '.'); ?></del>
                                </span>
                            <?php endif; ?>
                            <span class="h3 fw-bold text-primary mb-0 d-block">
                                <span class="visually-hidden">Precio actual: </span>
                                $ <?= number_format($precioFinal, 2, ',', '.'); ?>
                            </span>
                            <small class="text-muted d-block">por pasajero</small>
                        </div>

                        <div class="col-12 col-md-3">
                            <div class="d-grid mb-2">
                                <a href="/tpi/public/detalle_vuelo.php?id=<?= $vuelo['codVuelo']; ?>" class="btn btn-outline-primary btn-sm fw-semibold">
                                    Ver detalles <span class="visually-hidden">del vuelo de <?= htmlspecialchars($vuelo['origenVuelo']); ?> a <?= htmlspecialchars($vuelo['destinoVuelo']); ?></span>
                                </a>
                            </div>

                            <form action="/tpi/pasajero/procesar_reserva.php" method="POST" class="d-grid gap-2">
                                <input type="hidden" name="id" value="<?= $vuelo['codVuelo']; ?>">

                                <div>
                                    <label for="cantidad-<?= $vuelo['codVuelo']; ?>" class="form-label small fw-semibold text-muted mb-1">
                                        Cantidad de pasajes:
                                    </label>
                                    <input type="number" 
                                        id="cantidad-<?= $vuelo['codVuelo']; ?>" 
                                        name="cantidad" 
                                        class="form-control form-control-sm text-center" 
                                        value="1" 
                                        min="1" 
                                        max="<?= $maxPasajes; ?>" 
                                        required>
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm fw-bold">
                                    Reservar <span class="visually-hidden">vuelo de <?= htmlspecialchars($vuelo['origenVuelo']); ?> a <?= htmlspecialchars($vuelo['destinoVuelo']); ?></span>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </article>
        <?php endwhile; ?>

        <?php if ($total_paginas > 1): ?>
            <?php 
            $url_filtros = "&origen=" . urlencode($origen) . "&destino=" . urlencode($destino) . "&fecha=" . urlencode($fecha) . "&aerolinea=" . urlencode($aerolinea);
            ?>
            <nav aria-label="Navegación de resultados de vuelos">
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
                            <a class="page-link" href="?pagina=<?= ($pagina - 1) . $url_filtros; ?>">
                                <span aria-hidden="true">&laquo;</span>
                                <span class="visually-hidden">Ir a la página anterior</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <?php if ($pagina == $i): ?>
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">
                                    <?= $i; ?> 
                                    <span class="visually-hidden">(página actual)</span>
                                </span>
                            </li>
                        <?php else: ?>
                            <li class="page-item">
                                <a class="page-link" href="?pagina=<?= $i . $url_filtros; ?>">
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
                            <a class="page-link" href="?pagina=<?= ($pagina + 1) . $url_filtros; ?>">
                                <span aria-hidden="true">&raquo;</span>
                                <span class="visually-hidden">Ir a la página siguiente</span>
                            </a>
                        </li>
                    <?php endif; ?>

                </ul>
            </nav>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-info text-center p-4 shadow-sm border-0">
            <h3 class="h5 mb-0">No se encontraron vuelos disponibles</h3>
            <p class="mt-2 mb-0">Probá ajustando las ciudades o la fecha de búsqueda para ver más resultados.</p>
        </div>
    <?php endif; ?>

</div>

<?php require '../includes/footer.php'; ?>