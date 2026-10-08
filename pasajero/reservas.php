<?php
require '../includes/sesiones.php';
checkRole('usuario');
require '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3 col-lg-2">
        <div class="list-group shadow-sm mb-4">
            <a href="/tpi/pasajero/reservas.php" class="list-group-item list-group-item-action active">Mis Reservas</a>
            <a href="/tpi/pasajero/historial.php" class="list-group-item list-group-item-action">Historial de Compras</a>
            <a href="/tpi/pasajero/perfil.php" class="list-group-item list-group-item-action">Mi Perfil</a>
        </div>
    </div>
    
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Portada</a></li>
                <li class="breadcrumb-item"><a href="#">Panel Pasajero</a></li>
                <li class="breadcrumb-item active" aria-current="page">Mis Reservas</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="card-title mb-4">Reservas del Usuario</h4>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Vuelo</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>