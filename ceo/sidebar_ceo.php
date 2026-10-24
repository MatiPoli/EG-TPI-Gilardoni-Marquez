<?php
$active_page = isset($active_page) ? $active_page : 'dashboard';
?>
<div class="col-md-3 col-lg-2 mb-4 d-print-none">
    <div class="list-group shadow-sm">
        <a href="/tpi/ceo/dashboard.php" class="list-group-item list-group-item-action <?= $active_page == 'dashboard' ? 'active' : ''; ?>">Panel General</a>
        <a href="/tpi/ceo/reporte_ventas.php" class="list-group-item list-group-item-action <?= $active_page == 'reporte_ventas' ? 'active' : ''; ?>">Reporte de Ventas</a>
        <a href="/tpi/ceo/reporte_ocupacion.php" class="list-group-item list-group-item-action <?= $active_page == 'reporte_ocupacion' ? 'active' : ''; ?>">Reporte de Ocupación</a>
        <a href="/tpi/ceo/vuelos/abm_vuelos.php" class="list-group-item list-group-item-action <?= $active_page == 'vuelos' ? 'active' : ''; ?>">Gestión de Vuelos</a>
        <a href="/tpi/ceo/promociones/abm_promociones.php" class="list-group-item list-group-item-action <?= $active_page == 'promociones' ? 'active' : ''; ?>">Promociones</a>
    </div>
</div>