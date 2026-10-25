<?php
require '../includes/sesiones.php';
checkRole('ceo');
require '../includes/conexion.php';

$airline_id = $_SESSION['codAerolinea'];

$fecha_inicio = isset($_GET['fecha_inicio']) ? trim($_GET['fecha_inicio']) : '';
$fecha_fin = isset($_GET['fecha_fin']) ? trim($_GET['fecha_fin']) : '';

$condiciones = ["v.codAerolinea = ?", "r.estadoReserva = 'confirmada'"];
$params = [$airline_id];
$tipos = "i";

if (!empty($fecha_inicio)) {
    $condiciones[] = "r.fechaReserva >= ?";
    $params[] = $fecha_inicio;
    $tipos .= "s";
}
if (!empty($fecha_fin)) {
    $condiciones[] = "r.fechaReserva <= ?";
    $params[] = $fecha_fin;
    $tipos .= "s";
}

$where_sql = "WHERE " . implode(" AND ", $condiciones);

$sql_resumen = "SELECT 
                    COUNT(r.codReserva) AS total_operaciones,
                    IFNULL(SUM(r.cantidadPasajes), 0) AS total_pasajes_vendidos,
                    IFNULL(SUM(r.precioTotal), 0) AS facturacion_total,
                    IFNULL(AVG(r.precioTotal), 0) AS ticket_promedio
                FROM RESERVAS r
                INNER JOIN VUELOS v ON r.codVuelo = v.codVuelo
                $where_sql";

$stmt_resumen = $conexion->prepare($sql_resumen);
$stmt_resumen->bind_param($tipos, ...$params);
$stmt_resumen->execute();
$resumen = $stmt_resumen->get_result()->fetch_assoc();

$hay_datos = ($resumen['total_operaciones'] > 0);

$ventas_por_fecha = [];
$ventas_por_destino = [];

if ($hay_datos) {
    $sql_fechas = "SELECT r.fechaReserva, SUM(r.precioTotal) AS total_dia
                   FROM RESERVAS r
                   INNER JOIN VUELOS v ON r.codVuelo = v.codVuelo
                   $where_sql
                   GROUP BY r.fechaReserva
                   ORDER BY r.fechaReserva ASC";
    $stmt_fechas = $conexion->prepare($sql_fechas);
    $stmt_fechas->bind_param($tipos, ...$params);
    $stmt_fechas->execute();
    $res_fechas = $stmt_fechas->get_result();
    while ($f = $res_fechas->fetch_assoc()) {
        $ventas_por_fecha[date('d/m', strtotime($f['fechaReserva']))] = (float)$f['total_dia'];
    }

    $sql_destinos = "SELECT v.destinoVuelo, SUM(r.precioTotal) AS total_destino
                     FROM RESERVAS r
                     INNER JOIN VUELOS v ON r.codVuelo = v.codVuelo
                     $where_sql
                     GROUP BY v.destinoVuelo
                     ORDER BY total_destino DESC";
    $stmt_destinos = $conexion->prepare($sql_destinos);
    $stmt_destinos->bind_param($tipos, ...$params);
    $stmt_destinos->execute();
    $res_destinos = $stmt_destinos->get_result();
    while ($d = $res_destinos->fetch_assoc()) {
        $ventas_por_destino[$d['destinoVuelo']] = (float)$d['total_destino'];
    }
}

$title = "Reporte de Ventas | Panel CEO";
require '../includes/header.php';
?>

<div class="row">
    
    <?php 
    $active_page = 'reporte_ventas';
    require 'sidebar_ceo.php'; 
    ?>

    <div class="col-12 col-md-9 col-lg-10">
        
        <nav aria-label="breadcrumb" class="d-print-none">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="/tpi/ceo/dashboard.php">Panel CEO</a></li>
                <li class="breadcrumb-item active" aria-current="page">Reporte de Ventas</li>
            </ol>
        </nav>

        <h1 class="h3 fw-bold text-dark mb-4 text-center text-md-start">Reporte de Ventas y Flujo Monetario</h1>

        <div class="card shadow-sm border-0 bg-light mb-4 d-print-none">
            <div class="card-body p-4">
                <form action="/tpi/ceo/reporte_ventas.php" method="GET" class="row g-3 align-items-end">
                    <fieldset class="row g-3 align-items-end border-0 p-0 m-0">
                        <legend class="h6 fw-bold text-dark mb-3">Filtros de período</legend>
                    <div class="col-12 col-md-4">
                        <label for="fecha_inicio" class="form-label small fw-semibold text-dark">Fecha Inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio); ?>">
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="fecha_fin" class="form-label small fw-semibold text-dark">Fecha Fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin); ?>">
                    </div>

                    <div class="col-12 col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary fw-bold w-100">Aplicar filtros</button>
                        <a href="/tpi/ceo/reporte_ventas.php" class="btn btn-outline-dark w-100">Limpiar filtros</a>
                    </div>
                    </fieldset>
                </form>
            </div>
        </div>

        <?php if ($hay_datos): ?>
            <div class="card shadow-sm border-0 mb-4 p-4 bg-white">
                
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light border rounded text-center">
                            <span class="text-dark small text-uppercase d-block fw-bold mb-1">Facturación Total</span>
                            <span class="fs-5 fw-bold text-success text-nowrap d-block">
                                $ <?= number_format($resumen['facturacion_total'], 2, ',', '.'); ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light border rounded text-center">
                            <span class="text-dark small text-uppercase d-block fw-bold mb-1">Pasajes Facturados</span>
                            <span class="fs-5 fw-bold text-primary text-nowrap d-block">
                                <?= $resumen['total_pasajes_vendidos']; ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light border rounded text-center">
                            <span class="text-dark small text-uppercase d-block fw-bold mb-1">Ticket Promedio</span>
                            <span class="fs-5 fw-bold text-dark text-nowrap d-block">
                                $ <?= number_format($resumen['ticket_promedio'], 2, ',', '.'); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row g-4 my-2">
                    <div class="col-12 col-lg-6">
                        <div class="p-3 border rounded text-center h-100 bg-white">
                            <div style="position: relative; height: 230px;">
                                <canvas id="graficoEvolucion" role="img" aria-labelledby="titulo-grafico-evolucion"></canvas>
                            </div>
                            <h2 id="titulo-grafico-evolucion" class="h6 fw-bold text-dark mt-3 mb-0">Evolución de Facturación Diaria</h2>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="p-3 border rounded text-center h-100 bg-white">
                            <div style="position: relative; height: 230px;">
                                <canvas id="graficoDestinos" role="img" aria-labelledby="titulo-grafico-destinos"></canvas>
                            </div>
                            <h2 id="titulo-grafico-destinos" class="h6 fw-bold text-dark mt-3 mb-0">Distribución de Ingresos por Destino</h2>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4 mb-2 d-print-none">
                    <button type="button" class="btn btn-dark fw-bold px-4" onclick="window.print();">
                        Imprimir reporte de ventas
                    </button>
                </div>

            </div>
        <?php else: ?>
            <div class="alert alert-info text-center p-4 shadow-sm border-0" role="alert" aria-live="assertive">
                <h2 class="h5 mb-0">No se encontraron suficientes datos</h2>
                <p class="mt-2 mb-0">No existen ventas confirmadas registradas en el período seleccionado para tu aerolínea.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php if ($hay_datos): ?>
<script>
    const ctxEvolucion = document.getElementById('graficoEvolucion').getContext('2d');
    new Chart(ctxEvolucion, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_keys($ventas_por_fecha)); ?>,
            datasets: [{
                data: <?= json_encode(array_values($ventas_por_fecha)); ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });

    const ctxDestinos = document.getElementById('graficoDestinos').getContext('2d');
    new Chart(ctxDestinos, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($ventas_por_destino)); ?>,
            datasets: [{
                data: <?= json_encode(array_values($ventas_por_destino)); ?>,
                backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#0dcaf0', '#6c757d']
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