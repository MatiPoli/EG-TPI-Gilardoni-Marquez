<?php

require '../../includes/sesiones.php';
checkRole('asset');
require '../../includes/header.php';
?>

<div class="table-responsive">
    <table class="table table-striped table-hover" aria-labelledby="caption-tabla">
        <thead>
            <tr>
                <th scope="col">Número</th>
                <th scope="col">Columna 1</th>
                <th scope="col">Columna 2</th>
                <th scope="col" class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th scope="row">Fila 1</th>
                <td>Celda 1-1</td>
                <td>Celda 1-2</td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-success fw-bold">Pagar</button></td>
            </tr>
            <tr>
                <th scope="row">Fila 2</th>
                <td>Celda 2-1</td>
                <td>Celda 2-2</td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-success fw-bold">Pagar</button></td>
            </tr>
        </tbody>
    </table>
</div>
<p class="text-center" id="caption-tabla">Descripción de la tabla</p>

        <?php if ($total_paginas > 1): ?>
            <?php 
            $url_filtros = "&origen=" . urlencode($origen) . "&destino=" . urlencode($destino) . "&fecha=" . urlencode($fecha) . "&aerolinea=" . urlencode($aerolinea);
            ?>
            <nav aria-label="Navegación de resultados de vuelos">
                <ul class="pagination justify-content-center">
                    
                    <?php if ($pagina <= 1): ?>
                        <li class="page-item disabled">
                            <span class="page-link" aria-disabled="true">
                                <span aria-hidden="true">&laquo;</span>
                                <span class="visually-hidden">Página anterior (deshabilitada)</span>
                            </span>
                        </li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link" href="?pagina=<?= ($pagina - 1) . $url_filtros; ?>">
                                <span aria-hidden="true">&laquo;</span>
                                <span class="visually-hidden">Ir a la página anterior</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <?php if ($pagina == $i): ?>
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">
                                    <?= $i; ?> 
                                    <span class="visually-hidden">(página actual)</span>
                                </span>
                            </li>
                        <?php else: ?>
                            <li class="page-item">
                                <a class="page-link" href="?pagina=<?= $i . $url_filtros; ?>">
                                    <span class="visually-hidden">Ir a la página </span><?= $i; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($pagina >= $total_paginas): ?>
                        <li class="page-item disabled">
                            <span class="page-link" aria-disabled="true">
                                <span aria-hidden="true">&raquo;</span>
                                <span class="visually-hidden">Página siguiente (deshabilitada)</span>
                            </span>
                        </li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link" href="?pagina=<?= ($pagina + 1) . $url_filtros; ?>">
                                <span aria-hidden="true">&raquo;</span>
                                <span class="visually-hidden">Ir a la página siguiente</span>
                            </a>
                        </li>
                    <?php endif; ?>

                </ul>
            </nav>
        <?php endif; ?>