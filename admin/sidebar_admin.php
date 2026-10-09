<?php
$active_page = isset($active_page) ? $active_page : 'dashboard';
?>
<div class="col-md-3 col-lg-2 mb-4">
    <div class="list-group shadow-sm">
        <a href="/tpi/admin/dashboard.php" class="list-group-item list-group-item-action <?php echo $active_page == 'dashboard' ? 'active' : ''; ?>">Panel General</a>
        <a href="/tpi/admin/aprobar_ceos.php" class="list-group-item list-group-item-action <?php echo $active_page == 'ceos' ? 'active' : ''; ?>">Solicitudes CEO</a>
        <a href="/tpi/admin/aerolineas/abm_aerolineas.php" class="list-group-item list-group-item-action <?php echo $active_page == 'aerolineas' ? 'active' : ''; ?>">Aerolíneas</a>
        <a href="/tpi/admin/auditoria.php" class="list-group-item list-group-item-action <?php echo $active_page == 'auditoria' ? 'active' : ''; ?>">Auditar Promos</a>
        <a href="/tpi/admin/novedades/abm_novedades.php" class="list-group-item list-group-item-action <?php echo $active_page == 'novedades' ? 'active' : ''; ?>">Novedades</a>
        <a href="/tpi/admin/reportes.php" class="list-group-item list-group-item-action <?php echo $active_page == 'reportes' ? 'active' : ''; ?>">Reportes Globales</a>
    </div>
</div>