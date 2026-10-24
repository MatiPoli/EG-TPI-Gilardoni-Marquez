<?php
require '../includes/sesiones.php';
checkRole('ceo');
require '../includes/conexion.php';

$airline_id = $_SESSION['codAerolinea'];

$fecha_inicio = isset($_GET['fecha_inicio']) ? trim($_GET['fecha_inicio']) : '';
$fecha_fin = isset($_GET['fecha_fin']) ? trim($_GET['fecha_fin']) : '';

$condiciones = ["v.codAerolinea = ?"];
$params = [$airline_id];
$tipos = "i";

if (!empty($fecha_inicio)) {
    $condiciones[] = "v.fechaSalidaVuelo >= ?";
    $params[] = $fecha_inicio;
    $tipos .= "s";
}
if (!empty($fecha_fin)) {
    $condiciones[] = "v.fechaSalidaVuelo <= ?";
    $params[] = $fecha_fin;
    $tipos .= "s";
}

$where_sql = "WHERE " . implode(" AND ", $condiciones);

$sql_vuelos = "SELECT 
                    v.codVuelo, v.origenVuelo, v.destinoVuelo, v.fechaSalidaVuelo, v.asientosDisponibles,
                    IFNULL(SUM(CASE WHEN r.estadoReserva = 'confirmada' THEN r.cantidadPasajes ELSE 0 END), 0) AS asientos_ocupados
               FROM VUELOS v
               LEFT JOIN RESERVAS r ON v.codVuelo = r.codVuelo
               $where_sql
               GROUP BY v.codVuelo
               ORDER BY v.fechaSalidaVuelo ASC";

$stmt_vuelos = $conexion->prepare($sql_vuelos);
$stmt_vuelos->bind_param($tipos, ...$params);
$stmt_vuelos->execute();
$res_vuelos = $stmt_vuelos->get_result();

$vuelos = [];
$total_capacidad = 0;
$total_ocupados = 0;
$nombres_vuelos = [];
$porcentajes_vuelos = [];

while ($row = $res_vuelos->fetch_assoc()) {
    $ocupados = (int)$row['asientos_ocupados'];
    $disponibles = (int)$row['asientosDisponibles'];
    $capacidad = $ocupados + $disponibles;

    $porcentaje = ($capacidad > 0) ? round(($ocupados / $capacidad) * 100, 1) : 0;
    
    $row['capacidad'] = $capacidad;
    $row['porcentaje'] = $porcentaje;
    $vuelos[] = $row;

    $total_capacidad += $capacidad;
    $total_ocupados += $ocupados;

    $nombres_vuelos[] = "#" . $row['codVuelo'] . " " . $row['destinoVuelo'];
    $porcentajes_vuelos[] = $porcentaje;
}

$hay_datos = (count($vuelos) > 0);
$ocupacion_global = ($total_capacidad > 0) ? round(($total_ocupados / $total_capacidad) * 100, 1) : 0;

$title = "Reporte de Ocupación | Panel CEO";
require '../includes/header.php';
?>

<div class="row">
    
    <?php 
    $active_page = 'reporte_ocupacion';
    require 'sidebar_ceo.php'; 
    ?>

    <div class="col-12 col-md-9 col-lg-10">
        
        <nav aria-label="breadcrumb" class="d-print-none">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="/tpi/ceo/dashboard.php">Panel CEO</a></li>
                <li class="breadcrumb-item active" aria-current="page">Reporte de Ocupación</li>
            </ol>
        </nav>

        <h1 class="h3 fw-bold text-dark mb-4 text-center text-md-start">Reporte de Ocupación de Vuelos</h1>

        <div class="card shadow-sm border-0 bg-secondary mb-4 d-print-none">
            <div class="card-body p-4">
                <h2 class="h6 fw-bold text-primary mb-3">Filtros de Período de Vuelo</h2>
                <form action="/tpi/ceo/reporte_ocupacion.php" method="GET" class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label for="fecha_inicio" class="form-label small fw-semibold text-dark">Fecha Inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio); ?>">
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="fecha_fin" class="form-label small fw-semibold text-dark">Fecha Fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin); ?>">
                    </div>

                    <div class="col-12 col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary fw-bold w-100">Aplicar</button>
                        <a href="/tpi/ceo/reporte_ocupacion.php" class="btn btn-outline-dark w-100">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($hay_datos): ?>
            <div class="card shadow-sm border-0 mb-4 p-4 bg-white">
                
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light border rounded text-center">
                            <span class="text-muted small text-uppercase d-block fw-bold mb-1">Ocupación Promedio</span>
                            <span class="fs-5 fw-bold text-primary text-nowrap d-block"><?= $ocupacion_global; ?>%</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light border rounded text-center">
                            <span class="text-muted small text-uppercase d-block fw-bold mb-1">Asientos Ocupados</span>
                            <span class="fs-5 fw-bold text-success text-nowrap d-block"><?= $total_ocupados; ?></span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light border rounded text-center">
                            <span class="text-muted small text-uppercase d-block fw-bold mb-1">Capacidad Total Ofertada</span>
                            <span class="fs-5 fw-bold text-dark text-nowrap d-block"><?= $total_capacidad; ?></span>
                        </div>
                    </div>
                </div>

                <div class="row g-3 my-2">
                    <div class="col-12 col-md-7">
                        <div class="p-3 border rounded text-center h-100 bg-white">
                            <div style="position: relative; height: 210px;">
                                <canvas id="graficoBarrasOcupacion"></canvas>
                            </div>
                            <h3 class="h6 fw-bold text-muted mt-2 mb-0">Porcentaje de Ocupación por Vuelo</h3>
                        </div>
                    </div>

                    <div class="col-12 col-md-5">
                        <div class="p-3 border rounded text-center h-100 bg-white">
                            <div style="position: relative; height: 210px;">
                                <canvas id="graficoTortaGlobal"></canvas>
                            </div>
                            <h3 class="h6 fw-bold text-muted mt-2 mb-0">Asientos Vendidos vs Libres</h3>
                        </div>
                    </div>
                </div>

                <div class="bloque-tabla-imprimir mt-4">
                    <h3 class="h6 fw-bold text-muted mb-3">Detalle de Cupos por Vuelo</h3>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Vuelo</th>
                                    <th>Ruta</th>
                                    <th>Fecha de Salida</th>
                                    <th class="text-center">Ocupados</th>
                                    <th class="text-center">Disponibles</th>
                                    <th style="min-width: 140px;">% Ocupación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vuelos as $v): ?>
                                    <tr>
                                        <td>#<?= $v['codVuelo']; ?></td>
                                        <td><?= htmlspecialchars($v['origenVuelo'] . ' → ' . $v['destinoVuelo']); ?></td>
                                        <td><?= date('d/m/Y', strtotime($v['fechaSalidaVuelo'])); ?></td>
                                        <td class="text-center fw-bold text-success"><?= $v['asientos_ocupados']; ?></td>
                                        <td class="text-center"><?= $v['asientosDisponibles']; ?></td>
                                        <td>
                                            <div class="progress" style="height: 18px;">
                                                <div class="progress-bar bg-primary fw-bold" role="progressbar" 
                                                     style="width: <?= $v['porcentaje']; ?>%;" 
                                                     aria-valuenow="<?= $v['porcentaje']; ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <?= $v['porcentaje']; ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="text-center mt-4 mb-2 d-print-none">
                    <button type="button" class="btn btn-dark fw-bold px-4" onclick="window.print();">
                        Imprimir
                    </button>
                </div>

            </div>
        <?php else: ?>
            <div class="alert alert-info text-center p-4 shadow-sm border-0">
                <h3 class="h5 mb-0">No se encontraron suficientes datos</h3>
                <p class="mt-2 mb-0">No existen vuelos programados para tu aerolínea en el rango de fechas seleccionado.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php if ($hay_datos): ?>
<script>
    const ctxBarras = document.getElementById('graficoBarrasOcupacion').getContext('2d');
    new Chart(ctxBarras, {
        type: 'bar',
        data: {
            labels: <?= json_encode($nombres_vuelos); ?>,
            datasets: [{
                label: '% Ocupación',
                data: <?= json_encode($porcentajes_vuelos); ?>,
                backgroundColor: '#0d6efd'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, max: 100 }
            }
        }
    });

    const ctxTorta = document.getElementById('graficoTortaGlobal').getContext('2d');
    new Chart(ctxTorta, {
        type: 'pie',
        data: {
            labels: ['Ocupados', 'Libres'],
            datasets: [{
                data: [<?= $total_ocupados; ?>, <?= ($total_capacidad - $total_ocupados); ?>],
                backgroundColor: ['#198754', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>