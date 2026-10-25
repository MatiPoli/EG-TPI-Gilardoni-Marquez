<?php
require '../includes/sesiones.php';
checkRole('administrador');
require '../includes/conexion.php';
$inicio = filter_input(INPUT_GET, 'fecha_inicio', FILTER_UNSAFE_RAW);
$fin = filter_input(INPUT_GET, 'fecha_fin', FILTER_UNSAFE_RAW);
$inicio = is_string($inicio) ? trim($inicio) : '';
$fin = is_string($fin) ? trim($fin) : '';
$condiciones = ['1=1'];
$params = [];
$tipos = '';
$tieneFechaRegistro = false;
$resultadoColumna = $conexion->query("SELECT COUNT(*) AS existe FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'USUARIOS' AND COLUMN_NAME = 'fechaRegistro'");
if ($resultadoColumna && (int) $resultadoColumna->fetch_assoc()['existe'] > 0) {
    $tieneFechaRegistro = true;
}
if ($tieneFechaRegistro && $inicio !== '') {
    $condiciones[] = 'DATE(u.fechaRegistro) >= ?';
    $params[] = $inicio;
    $tipos .= 's';
}
if ($tieneFechaRegistro && $fin !== '') {
    $condiciones[] = 'DATE(u.fechaRegistro) <= ?';
    $params[] = $fin;
    $tipos .= 's';
}
$where = implode(' AND ', $condiciones);
$columnas = "SELECT COUNT(*) total, SUM(tipoUsuario='usuario') pasajeros, SUM(tipoUsuario='ceo') ceos, 
    SUM(tipoUsuario='administrador') administradores, 
    SUM(estadoUsuario='activo') activos, SUM(estadoUsuario='pendiente') pendientes, 
    SUM(estadoUsuario='suspendido') suspendidos FROM USUARIOS u WHERE $where";
$stmt = $conexion->prepare($columnas);
if (!$stmt) {
    die('No se pudo preparar el reporte de usuarios.');
}
if ($tipos !== '') {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$datos = $stmt->get_result()->fetch_assoc();
$title = 'Reporte Global de Usuarios | ViajAir';
require '../includes/header.php';
?>
<div class="row">
    <?php
    $active_page = 'reporte_usuarios';
    require 'sidebar_admin.php';
    ?>
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb" class="d-print-none">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="/tpi/admin/dashboard.php">Administración</a></li>
                <li class="breadcrumb-item active" aria-current="page">Reporte de Usuarios</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold text-dark mb-4">Reporte Global de Usuarios</h1>
        <?php if (($inicio !== '' || $fin !== '') && !$tieneFechaRegistro): ?>
            <div class="alert alert-warning" role="alert" aria-live="assertive">
                El filtro por fecha requiere aplicar la actualización de la base de datos que agrega la fecha de registro de las cuentas.
            </div>
        <?php endif; ?>
        <div class="card shadow-sm border-0 bg-light mb-4 d-print-none">
            <div class="card-body p-4">
                <form method="GET">
                    <fieldset class="row g-3 align-items-end border-0 p-0 m-0">
                        <legend class="h6 fw-bold text-dark mb-3">Filtros de período</legend>
                        <div class="col-12 col-md-4">
                            <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($inicio); ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="fecha_fin" class="form-label">Fecha fin</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fin); ?>">
                        </div>
                        <div class="col-12 col-md-4 d-flex gap-2">
                            <button class="btn btn-primary" type="submit">Aplicar</button>
                            <a class="btn btn-outline-secondary" href="reporte_usuarios.php">Limpiar</a>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
        <div class="card shadow-sm border-0 mb-4 p-4 bg-white">
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4"><div class="p-3 bg-light border rounded text-center"><span class="text-dark small text-uppercase d-block fw-bold mb-1">Total de usuarios</span><span class="fs-5 fw-bold text-primary d-block"><?= (int) $datos['total']; ?></span></div></div>
                <div class="col-12 col-md-4"><div class="p-3 bg-light border rounded text-center"><span class="text-dark small text-uppercase d-block fw-bold mb-1">Pasajeros registrados</span><span class="fs-5 fw-bold text-success d-block"><?= (int) $datos['pasajeros']; ?></span></div></div>
                <div class="col-12 col-md-4"><div class="p-3 bg-light border rounded text-center"><span class="text-dark small text-uppercase d-block fw-bold mb-1">Cuentas CEO</span><span class="fs-5 fw-bold text-dark d-block"><?= (int) $datos['ceos']; ?></span></div></div>
            </div>
            <div class="row g-4 my-2">
                <div class="col-12 col-lg-6">
                    <div class="p-3 border rounded text-center h-100 bg-white">
                        <div style="position: relative; height: 230px;"><canvas id="roles" role="img" aria-labelledby="titulo-roles"></canvas></div>
                        <h2 id="titulo-roles" class="h6 fw-bold text-dark mt-3 mb-0">Usuarios por rol</h2>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="p-3 border rounded text-center h-100 bg-white">
                        <div style="position: relative; height: 230px;"><canvas id="estados" role="img" aria-labelledby="titulo-estados"></canvas></div>
                        <h2 id="titulo-estados" class="h6 fw-bold text-dark mt-3 mb-0">Estado de las cuentas</h2>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4 d-print-none">
                <button type="button" class="btn btn-dark fw-bold px-4" onclick="window.print();">Imprimir reporte de usuarios</button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('roles'), {
        type: 'bar',
        data: {
            labels: ['Pasajeros', 'CEO', 'Administradores'],
            datasets: [{
                data: [<?= (int) $datos['pasajeros']; ?>, <?= (int) $datos['ceos']; ?>, <?= (int) $datos['administradores']; ?>],
                backgroundColor: ['#0d6efd', '#198754', '#6c757d']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } }
    });
    new Chart(document.getElementById('estados'), {
        type: 'doughnut',
        data: {
            labels: ['Activas', 'Pendientes', 'Suspendidas'],
            datasets: [{
                data: [<?= (int) $datos['activos']; ?>, <?= (int) $datos['pendientes']; ?>, <?= (int) $datos['suspendidos']; ?>],
                backgroundColor: ['#198754', '#ffc107', '#dc3545']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
<?php require '../includes/footer.php'; ?>
