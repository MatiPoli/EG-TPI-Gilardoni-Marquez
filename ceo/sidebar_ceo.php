<?php
$active_page = isset($active_page) ? $active_page : 'dashboard';
?>
<div class="col-md-3 col-lg-2 mb-4">
    <div class="list-group shadow-sm">
        <a href="/tpi/ceo/dashboard.php" class="list-group-item list-group-item-action <?php echo $active_page == 'dashboard' ? 'active' : ''; ?>">Panel General</a>
        <a href="/tpi/ceo/vuelos/abm_vuelos.php" class="list-group-item list-group-item-action <?php echo $active_page == 'vuelos' ? 'active' : ''; ?>">Gestión de Vuelos</a>
        <a href="/tpi/ceo/promociones/abm_promociones.php" class="list-group-item list-group-item-action <?php echo $active_page == 'promociones' ? 'active' : ''; ?>">Promociones</a>
        <a href="/tpi/ceo/reportes.php" class="list-group-item list-group-item-action <?php echo $active_page == 'reportes' ? 'active' : ''; ?>">Reportes</a>
    </div>
</div>