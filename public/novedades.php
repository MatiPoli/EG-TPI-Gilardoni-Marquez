<?php
require '../includes/sesiones.php';
require '../includes/conexion.php';

$limite = 5;
$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) {
    $pagina = 1;
}
$offset = ($pagina - 1) * $limite;

$sql_total = "SELECT COUNT(*) AS total 
              FROM NOVEDADES 
              WHERE fechaPublicacionNovedad <= CURDATE() 
              AND fechaExpiracionNovedad >= CURDATE()";

$result_total = $conexion->query($sql_total);
$row_total = $result_total->fetch_assoc();
$total_registros = $row_total['total'];
$total_paginas = ceil($total_registros / $limite);

$sql = "SELECT tituloNovedad, textoNovedad, fechaPublicacionNovedad 
        FROM NOVEDADES 
        WHERE fechaPublicacionNovedad <= CURDATE() 
        AND fechaExpiracionNovedad >= CURDATE() 
        ORDER BY fechaPublicacionNovedad DESC 
        LIMIT $limite OFFSET $offset";

$result = $conexion->query($sql);

require '../includes/header.php';
?>

<div class="container mt-4 mb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Novedades</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <h1 class="mb-4 text-center text-black">Novedades y Comunicados</h1>

            <?php if ($result && $result->num_rows > 0): ?>
                <div class="list-group shadow-sm mb-4">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php 
                            $titulo = !empty($row['tituloNovedad']) ? $row['tituloNovedad'] : 'Aviso Operativo';
                        ?>
                        <div class="list-group-item flex-column align-items-start p-4">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                <h2 class="h5 mb-0 text-primary"><?php echo htmlspecialchars($titulo); ?></h2>
                                <span class="text-body-tertiary fw-bold small ms-2">
                                    <?php echo date('d/m/Y', strtotime($row['fechaPublicacionNovedad'])); ?>
                                </span>
                            </div>
                            <p class="mb-1 fs-5 text-dark"><?php echo htmlspecialchars($row['textoNovedad']); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>

                <?php if ($total_paginas > 1): ?>
                    <nav aria-label="Navegación de novedades">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?>" aria-label="Anterior">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?>" aria-label="Siguiente">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert alert-info text-center p-4 shadow-sm">
                    <h2 class="h5 mb-0">No hay novedades por el momento</h2>
                    <p class="mt-2 mb-0">Todos los servicios operan con normalidad en este momento.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>