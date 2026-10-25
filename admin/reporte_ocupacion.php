<?php
require '../includes/sesiones.php';
checkRole('administrador');
require '../includes/conexion.php';
$inicio = filter_input(INPUT_GET, 'fecha_inicio', FILTER_UNSAFE_RAW);
$fin = filter_input(INPUT_GET, 'fecha_fin', FILTER_UNSAFE_RAW);
$aerolinea = filter_input(INPUT_GET, 'aerolinea', FILTER_VALIDATE_INT);
$inicio = is_string($inicio) ? trim($inicio) : ''; $fin = is_string($fin) ? trim($fin) : '';
$aerolinea = ($aerolinea && $aerolinea > 0) ? $aerolinea : 0;
$aerolineas = $conexion->query('SELECT codAerolinea, nombreAerolinea, codigoIATA FROM AEROLINEAS ORDER BY nombreAerolinea');
$condiciones = ['1=1']; $params = []; $tipos = '';
if ($inicio !== '') {
    $condiciones[] = 'v.fechaSalidaVuelo >= ?';
    $params[] = $inicio;
    $tipos .= 's';
}
if ($fin !== '') {
    $condiciones[] = 'v.fechaSalidaVuelo <= ?';
    $params[] = $fin;
    $tipos .= 's';
}
if ($aerolinea > 0) {
    $condiciones[] = 'v.codAerolinea = ?';
    $params[] = $aerolinea;
    $tipos .= 'i';
}
$where = 'WHERE ' . implode(' AND ', $condiciones);
$sql = "SELECT v.codVuelo, v.origenVuelo, v.destinoVuelo, v.fechaSalidaVuelo, v.asientosDisponibles, a.nombreAerolinea, 
    COALESCE(SUM(CASE WHEN r.estadoReserva = 'confirmada' THEN r.cantidadPasajes ELSE 0 END),0) 
    ocupados FROM VUELOS v INNER JOIN AEROLINEAS a 
    ON a.codAerolinea = v.codAerolinea LEFT JOIN RESERVAS r 
    ON r.codVuelo = v.codVuelo $where GROUP BY v.codVuelo ORDER BY v.fechaSalidaVuelo";
$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die('No se pudo preparar el reporte de ocupación.');
}
if ($tipos !== '') {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$vuelos = [];
$capacidad = 0;
$ocupadosTotal = 0;
$labels = [];
$datos = [];
while ($v = $result->fetch_assoc()) {
    $v['capacidad'] = (int) $v['asientosDisponibles'] + (int) $v['ocupados'];
    $v['porcentaje'] = $v['capacidad'] ? round($v['ocupados'] / $v['capacidad'] * 100, 1) : 0;
    $vuelos[] = $v;
    $capacidad += $v['capacidad'];
    $ocupadosTotal += (int) $v['ocupados'];
    $labels[] = '#' . $v['codVuelo'] . ' ' . $v['destinoVuelo'];
    $datos[] = $v['porcentaje'];
}
$hayDatos = count($vuelos) > 0;
$ocupacion = $capacidad ? round($ocupadosTotal / $capacidad * 100, 1) : 0;
$title = 'Reporte Global de Ocupación | ViajAir';
require '../includes/header.php';
?>
<div class="row">
    <?php
    $active_page = 'reporte_ocupaciones';
    require 'sidebar_admin.php';
    ?>
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb" class="d-print-none">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="/tpi/admin/dashboard.php">Administración</a></li>
                <li class="breadcrumb-item active" aria-current="page">Reporte de Ocupación</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold text-dark mb-4">Reporte Global de Ocupación</h1>
        <div class="card shadow-sm border-0 bg-light mb-4 d-print-none">
            <div class="card-body p-4">
                <form method="GET">
                    <fieldset class="row g-3 align-items-end border-0 p-0 m-0">
                        <legend class="h6 fw-bold text-dark mb-3">Filtros de vuelos</legend>
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
                            <a class="btn btn-outline-secondary" href="reporte_ocupacion.php">Limpiar</a>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
        <?php if ($hayDatos): ?>
            <div class="card shadow-sm border-0 mb-4 p-4 bg-white">
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4"><div class="p-3 bg-light border rounded text-center"><span class="text-dark small text-uppercase d-block fw-bold mb-1">Ocupación promedio</span><span class="fs-5 fw-bold text-primary d-block"><?= $ocupacion; ?>%</span></div></div>
                    <div class="col-12 col-md-4"><div class="p-3 bg-light border rounded text-center"><span class="text-dark small text-uppercase d-block fw-bold mb-1">Asientos ocupados</span><span class="fs-5 fw-bold text-success d-block"><?= $ocupadosTotal; ?></span></div></div>
                    <div class="col-12 col-md-4"><div class="p-3 bg-light border rounded text-center"><span class="text-dark small text-uppercase d-block fw-bold mb-1">Capacidad total ofertada</span><span class="fs-5 fw-bold text-dark d-block"><?= $capacidad; ?></span></div></div>
                </div>
                <div class="row g-4 my-2">
                    <div class="col-12 col-lg-7">
                        <div class="p-3 border rounded text-center h-100 bg-white">
                            <div style="position: relative; height: 230px;"><canvas id="ocupacionVuelos" role="img" aria-labelledby="titulo-ocupacion-vuelos"></canvas></div>
                            <h2 id="titulo-ocupacion-vuelos" class="h6 fw-bold text-dark mt-3 mb-0">Porcentaje de ocupación por vuelo</h2>
                        </div>
                    </div>
                    <div class="col-12 col-lg-5">
                        <div class="p-3 border rounded text-center h-100 bg-white">
                            <div style="position: relative; height: 230px;"><canvas id="ocupacionGlobal" role="img" aria-labelledby="titulo-ocupacion-global"></canvas></div>
                            <h2 id="titulo-ocupacion-global" class="h6 fw-bold text-dark mt-3 mb-0">Asientos vendidos vs libres</h2>
                        </div>
                    </div>
                </div>
                <div class="bloque-tabla-imprimir mt-4">
                    <h2 class="h6 fw-bold text-dark mb-3">Detalle de ocupación</h2>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <caption class="visually-hidden">Detalle de vuelos y ocupación</caption>
                            <thead><tr><th scope="col">Código</th><th scope="col">Aerolínea</th><th scope="col">Ruta</th><th scope="col">Fecha</th><th scope="col">Ocupados</th><th scope="col">Disponibles</th><th scope="col">Ocupación</th></tr></thead>
                            <tbody>
                                <?php foreach ($vuelos as $v): ?>
                                    <tr>
                                        <th scope="row">#<?= (int) $v['codVuelo']; ?></th>
                                        <td><?= htmlspecialchars($v['nombreAerolinea']); ?></td>
                                        <td><?= htmlspecialchars($v['origenVuelo'] . ' → ' . $v['destinoVuelo']); ?></td>
                                        <td><?= date('d/m/Y', strtotime($v['fechaSalidaVuelo'])); ?></td>
                                        <td><?= (int) $v['ocupados']; ?></td>
                                        <td><?= (int) $v['asientosDisponibles']; ?></td>
                                        <td><div class="progress" style="height: 18px;"><div class="progress-bar" role="progressbar" aria-label="Ocupación: <?= $v['porcentaje']; ?> por ciento" style="width: <?= $v['porcentaje']; ?>%;" aria-valuenow="<?= $v['porcentaje']; ?>" aria-valuemin="0" aria-valuemax="100"><?= $v['porcentaje']; ?>%</div></div></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="text-center mt-4 d-print-none"><button type="button" class="btn btn-dark fw-bold px-4" onclick="window.print();">Imprimir reporte de ocupación</button></div>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center p-4 shadow-sm border-0" role="alert" aria-live="assertive">No existen vuelos para los filtros seleccionados.</div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php if ($hayDatos): ?>
<script>
    new Chart(document.getElementById('ocupacionVuelos'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels); ?>,
            datasets: [{ label: '% Ocupación', data: <?= json_encode($datos); ?>, backgroundColor: '#0d6efd' }]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 100 } } }
    });
    new Chart(document.getElementById('ocupacionGlobal'), {
        type: 'pie',
        data: {
            labels: ['Ocupados', 'Libres'],
            datasets: [{ data: [<?= $ocupadosTotal; ?>, <?= $capacidad - $ocupadosTotal; ?>], backgroundColor: ['#198754', '#6c757d'] }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
<?php endif; ?>
<?php require '../includes/footer.php'; ?>
