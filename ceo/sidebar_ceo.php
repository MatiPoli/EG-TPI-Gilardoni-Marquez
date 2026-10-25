<?php
$active_page = isset($active_page) ? $active_page : 'dashboard';
?>
<div class="col-md-3 col-lg-2 mb-4 d-print-none">
    <nav aria-label="Navegación del panel CEO">
        <div class="list-group shadow-sm">
            <a href="/tpi/ceo/dashboard.php" class="list-group-item list-group-item-action <?= $active_page == 'dashboard' ? 'active' : ''; ?>" <?= $active_page == 'dashboard' ? 'aria-current="page"' : ''; ?>>Panel General</a>
            <a href="/tpi/ceo/reporte_ventas.php" class="list-group-item list-group-item-action <?= $active_page == 'reporte_ventas' ? 'active' : ''; ?>" <?= $active_page == 'reporte_ventas' ? 'aria-current="page"' : ''; ?>>Reporte de Ventas</a>
            <a href="/tpi/ceo/reporte_ocupacion.php" class="list-group-item list-group-item-action <?= $active_page == 'reporte_ocupacion' ? 'active' : ''; ?>" <?= $active_page == 'reporte_ocupacion' ? 'aria-current="page"' : ''; ?>>Reporte de Ocupación</a>
            <a href="/tpi/ceo/vuelos/abm_vuelos.php" class="list-group-item list-group-item-action <?= $active_page == 'vuelos' ? 'active' : ''; ?>" <?= $active_page == 'vuelos' ? 'aria-current="page"' : ''; ?>>Gestión de Vuelos</a>
            <a href="/tpi/ceo/promociones/abm_promociones.php" class="list-group-item list-group-item-action <?= $active_page == 'promociones' ? 'active' : ''; ?>" <?= $active_page == 'promociones' ? 'aria-current="page"' : ''; ?>>Promociones</a>
        </div>
    </nav>
</div>