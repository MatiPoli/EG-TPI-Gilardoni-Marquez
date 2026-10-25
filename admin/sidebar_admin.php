<?php
$active_page = isset($active_page) ? $active_page : 'dashboard';
?>
<div class="col-md-3 col-lg-2 mb-4">
    <nav aria-label="Navegación del panel de administración">
        <div class="list-group shadow-sm">
            <a href="/tpi/admin/dashboard.php" class="list-group-item list-group-item-action <?= $active_page == 'dashboard' ? 'active' : ''; ?>" <?= $active_page == 'dashboard' ? 'aria-current="page"' : ''; ?>>Panel General</a>
            <a href="/tpi/admin/aprobar_ceos.php" class="list-group-item list-group-item-action <?= $active_page == 'ceos' ? 'active' : ''; ?>" <?= $active_page == 'ceos' ? 'aria-current="page"' : ''; ?>>Solicitudes CEO</a>
            <a href="/tpi/admin/aerolineas/abm_aerolineas.php" class="list-group-item list-group-item-action <?= $active_page == 'aerolineas' ? 'active' : ''; ?>" <?= $active_page == 'aerolineas' ? 'aria-current="page"' : ''; ?>>Gestión de Aerolíneas</a>
            <a href="/tpi/admin/promociones/auditoria.php" class="list-group-item list-group-item-action <?= $active_page == 'auditoria' ? 'active' : ''; ?>" <?= $active_page == 'auditoria' ? 'aria-current="page"' : ''; ?>>Auditar Promociones</a>
            <a href="/tpi/admin/novedades/abm_novedades.php" class="list-group-item list-group-item-action <?= $active_page == 'novedades' ? 'active' : ''; ?>" <?= $active_page == 'novedades' ? 'aria-current="page"' : ''; ?>>Gestión de Novedades</a>
            <a href="/tpi/admin/reportes.php" class="list-group-item list-group-item-action <?= $active_page == 'reportes' ? 'active' : ''; ?>" <?= $active_page == 'reportes' ? 'aria-current="page"' : ''; ?>>Reportes Globales</a>
        </div>
    </nav>
</div>