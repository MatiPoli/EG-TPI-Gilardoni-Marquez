<?php
http_response_code(404);

require __DIR__ . '/includes/sesiones.php';

$title = 'Página no encontrada | ViajAir';
require __DIR__ . '/includes/header.php';
?>

<div class="container text-center py-5">
    <h1 class="display-1 fw-bold text-primary">404</h1>
    <h2 class="h3 mb-3">Página no encontrada</h2>
    <p class="lead mb-4">
        La dirección que intentaste visitar no existe.
    </p>
    <a href="/tpi/index.php" class="btn btn-primary">
        Volver al inicio
    </a>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
