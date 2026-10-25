<?php
require '../includes/sesiones.php';
checkRole('administrador');
require '../includes/conexion.php';

$inicio = filter_input(INPUT_GET, 'fecha_inicio', FILTER_UNSAFE_RAW);
$fin = filter_input(INPUT_GET, 'fecha_fin', FILTER_UNSAFE_RAW);
$aerolinea = filter_input(INPUT_GET, 'aerolinea', FILTER_VALIDATE_INT);
$inicio = is_string($inicio) ? trim($inicio) : '';
$fin = is_string($fin) ? trim($fin) : '';
$aerolinea = ($aerolinea && $aerolinea > 0) ? $aerolinea : 0;

$aerolineas = $conexion->query('SELECT codAerolinea, nombreAerolinea, codigoIATA FROM AEROLINEAS ORDER BY nombreAerolinea');
$condiciones = ["r.estadoReserva = 'confirmada'"];
$params = [];
$tipos = '';
if ($inicio !== '') {
    $condiciones[] = 'r.fechaReserva >= ?';
    $params[] = $inicio;
    $tipos .= 's';
}
if ($fin !== '') {
    $condiciones[] = 'r.fechaReserva <= ?';
    $params[] = $fin;
    $tipos .= 's';
}
if ($aerolinea > 0) {
    $condiciones[] = 'v.codAerolinea = ?';
    $params[] = $aerolinea;
    $tipos .= 'i';
}
$where = 'WHERE ' . implode(' AND ', $condiciones);
$sql = "FROM RESERVAS r INNER JOIN VUELOS v ON r.codVuelo = v.codVuelo $where";
$stmt = $conexion->prepare("SELECT COUNT(*) operaciones, COALESCE(SUM(r.cantidadPasajes),0) pasajes, 
    COALESCE(SUM(r.precioTotal),0) facturacion, COALESCE(AVG(r.precioTotal),0) promedio $sql");
if (!$stmt) {
    die('No se pudo preparar el resumen de ventas.');
}
if ($tipos !== '') {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$resumen = $stmt->get_result()->fetch_assoc();
$hayDatos = (int) $resumen['operaciones'] > 0;
$porFecha = [];
$porDestino = [];
if ($hayDatos) {
    $stmt = $conexion->prepare("SELECT r.fechaReserva fecha, SUM(r.precioTotal) total $sql GROUP BY r.fechaReserva ORDER BY r.fechaReserva");
    if (!$stmt) {
        die('No se pudo preparar la evolución de ventas.');
    }
    if ($tipos !== '') {
        $stmt->bind_param($tipos, ...$params);
    }
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        $porFecha[date('d/m', strtotime($row['fecha']))] = (float) $row['total'];
    }
    $stmt = $conexion->prepare("SELECT v.destinoVuelo destino, SUM(r.precioTotal) total $sql GROUP BY v.destinoVuelo ORDER BY total DESC");
    if (!$stmt) {
        die('No se pudo preparar la distribución por destino.');
    }
    if ($tipos !== '') {
        $stmt->bind_param($tipos, ...$params);
    }
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        $porDestino[$row['destino']] = (float) $row['total'];
    }
}
$title = 'Reporte Global de Ventas | ViajAir';
require '../includes/header.php';
?>
<div class="row">
    <?php
    $active_page = 'reporte_ventas';
    require 'sidebar_admin.php';
    ?>
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb" class="d-print-none">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="/tpi/admin/dashboard.php">Administración</a></li>
                <li class="breadcrumb-item active" aria-current="page">Reporte de Ventas</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold text-dark mb-4">Reporte Global de Ventas</h1>

        <div class="card shadow-sm border-0 bg-light mb-4 d-print-none">
            <div class="card-body p-4">
                <form method="GET">
                    <fieldset class="row g-3 align-items-end border-0 p-0 m-0">
                        <legend class="h6 fw-bold text-dark mb-3">Filtros de ventas</legend>
                        <div class="col-12 col-md-3">
                            <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($inicio); ?>">
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="fecha_fin" class="form-label">Fecha fin</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fin); ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="aerolinea" class="form-label">Aerolínea</label>
                            <select id="aerolinea" name="aerolinea" class="form-select">
                                <option value="0">Todas</option>
                                <?php while ($a = $aerolineas->fetch_assoc()): ?>
                                    <option value="<?= (int) $a['codAerolinea']; ?>" <?= $aerolinea === (int) $a['codAerolinea'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($a['nombreAerolinea'] . ' (' . $a['codigoIATA'] . ')'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 d-flex gap-2">
                            <button class="btn btn-primary" type="submit">Aplicar</button>
                            <a class="btn btn-outline-secondary" href="reporte_ventas.php">Limpiar</a>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>

        <?php if ($hayDatos): ?>
            <div class="card shadow-sm border-0 mb-4 p-4 bg-white">
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light border rounded text-center">
                            <span class="text-dark small text-uppercase d-block fw-bold mb-1">Facturación total</span>
                            <span class="fs-5 fw-bold text-success text-nowrap d-block">$ <?= number_format($resumen['facturacion'], 2, ',', '.'); ?></span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light border rounded text-center">
                            <span class="text-dark small text-uppercase d-block fw-bold mb-1">Pasajes facturados</span>
                            <span class="fs-5 fw-bold text-primary text-nowrap d-block"><?= (int) $resumen['pasajes']; ?></span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light border rounded text-center">
                            <span class="text-dark small text-uppercase d-block fw-bold mb-1">Ticket promedio</span>
                            <span class="fs-5 fw-bold text-dark text-nowrap d-block">$ <?= number_format($resumen['promedio'], 2, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>
                <div class="row g-4 my-2">
                    <div class="col-12 col-lg-6">
                        <div class="p-3 border rounded text-center h-100 bg-white">
                            <div style="position: relative; height: 230px;">
                                <canvas id="ventasFecha" role="img" aria-labelledby="titulo-ventas-fecha"></canvas>
                            </div>
                            <h2 id="titulo-ventas-fecha" class="h6 fw-bold text-dark mt-3 mb-0">Evolución de facturación diaria</h2>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="p-3 border rounded text-center h-100 bg-white">
                            <div style="position: relative; height: 230px;">
                                <canvas id="ventasDestino" role="img" aria-labelledby="titulo-ventas-destino"></canvas>
                            </div>
                            <h2 id="titulo-ventas-destino" class="h6 fw-bold text-dark mt-3 mb-0">Distribución de ingresos por destino</h2>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4 mb-2 d-print-none">
                    <button type="button" class="btn btn-dark fw-bold px-4" onclick="window.print();">Imprimir reporte de ventas</button>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center p-4 shadow-sm border-0" role="alert" aria-live="assertive">
                <h2 class="h5 mb-0">No se encontraron datos</h2>
                <p class="mt-2 mb-0">No existen ventas confirmadas para los filtros seleccionados.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php if ($hayDatos): ?>
<script>
    new Chart(document.getElementById('ventasFecha'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_keys($porFecha)); ?>,
            datasets: [{
                data: <?= json_encode(array_values($porFecha)); ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });
    new Chart(document.getElementById('ventasDestino'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($porDestino)); ?>,
            datasets: [{
                data: <?= json_encode(array_values($porDestino)); ?>,
                backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#0dcaf0', '#6c757d']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
<?php endif; ?>
<?php require '../includes/footer.php'; ?>
