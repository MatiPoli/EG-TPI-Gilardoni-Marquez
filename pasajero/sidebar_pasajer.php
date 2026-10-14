<?php
$active_page = isset($active_page) ? $active_page : 'reservas';
?>
<div class="col-md-3 col-lg-2 mb-4">
    <div class="list-group shadow-sm">
        <a href="/tpi/pasajero/reservas.php" class="list-group-item list-group-item-action <?php echo $active_page == 'reservas' ? 'active' : ''; ?>">Mis Reservas</a>
        <a href="/tpi/pasajero/historial.php" class="list-group-item list-group-item-action <?php echo $active_page == 'historial' ? 'active' : ''; ?>">Historial de Compras</a>
        <a href="/tpi/pasajero/perfil.php" class="list-group-item list-group-item-action <?php echo $active_page == 'perfil' ? 'active' : ''; ?>">Mi Perfil</a>
    </div>
</div>