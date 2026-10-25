<?php
$active_page = isset($active_page) ? $active_page : 'dashboard';
?>
<div class="col-md-3 col-lg-2 mb-4">
    <nav aria-label="Navegación del panel de administración">
        <div class="list-group shadow-sm">
            <a href="/tpi/admin/dashboard.php" class="list-group-item list-group-item-action <?= $active_page == 'dashboard' ? 'active' : ''; ?>" <?= $active_page == 'dashboard' ? 'aria-current="page"' : ''; ?>>Panel General</a>
            <a href="/tpi/admin/reporte_ventas.php" class="list-group-item list-group-item-action <?= $active_page == 'reporte_ventas' ? 'active' : ''; ?>" <?= $active_page == 'reporte_ventas' ? 'aria-current="page"' : ''; ?>>Reporte de Ventas</a>
            <a href="/tpi/admin/reporte_ocupacion.php" class="list-group-item list-group-item-action <?= $active_page == 'reporte_ocupaciones' ? 'active' : ''; ?>" <?= $active_page == 'reporte_ocupaciones' ? 'aria-current="page"' : ''; ?>>Reporte de Ocupaciones</a>
            <a href="/tpi/admin/reporte_usuarios.php" class="list-group-item list-group-item-action <?= $active_page == 'reporte_usuarios' ? 'active' : ''; ?>" <?= $active_page == 'reporte_usuarios' ? 'aria-current="page"' : ''; ?>>Reporte de Usuarios</a>
            <a href="/tpi/admin/ceos/solicitudes.php" class="list-group-item list-group-item-action <?= $active_page == 'ceos' ? 'active' : ''; ?>" <?= $active_page == 'ceos' ? 'aria-current="page"' : ''; ?>>Solicitudes CEO</a>
            <a href="/tpi/admin/aerolineas/abm_aerolineas.php" class="list-group-item list-group-item-action <?= $active_page == 'aerolineas' ? 'active' : ''; ?>" <?= $active_page == 'aerolineas' ? 'aria-current="page"' : ''; ?>>Gestión de Aerolíneas</a>
            <a href="/tpi/admin/promociones/auditoria.php" class="list-group-item list-group-item-action <?= $active_page == 'auditoria' ? 'active' : ''; ?>" <?= $active_page == 'auditoria' ? 'aria-current="page"' : ''; ?>>Auditar Promociones</a>
            <a href="/tpi/admin/novedades/abm_novedades.php" class="list-group-item list-group-item-action <?= $active_page == 'novedades' ? 'active' : ''; ?>" <?= $active_page == 'novedades' ? 'aria-current="page"' : ''; ?>>Gestión de Novedades</a>
        </div>
    </nav>
</div>