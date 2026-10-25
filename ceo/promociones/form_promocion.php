<?php
require '../../includes/sesiones.php';
checkRole('ceo');
require '../../includes/conexion.php';

$codAerolinea = (int) $_SESSION['codAerolinea'];
$id = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
    : filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$id = ($id && $id > 0) ? $id : 0;
$errores = [];
$valores = ['descripcionPromocion' => '', 'descuentoPromocion' => ''];

if ($id > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $conexion->prepare(
        'SELECT descripcionPromocion, descuentoPromocion, estadoPromocion
         FROM PROMOCIONES WHERE codPromocion = ? AND codAerolinea = ?'
    );
    if (!$stmt) {
        die('No se pudo preparar la consulta de la promoción.');
    }
    $stmt->bind_param('ii', $id, $codAerolinea);
    $stmt->execute();
    $promocion = $stmt->get_result()->fetch_assoc();
    if (!$promocion || $promocion['estadoPromocion'] !== 'pendiente') {
        header('Location: abm_promociones.php');
        exit();
    }
    $valores = [
        'descripcionPromocion' => $promocion['descripcionPromocion'],
        'descuentoPromocion' => $promocion['descuentoPromocion'],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = isset($_POST['descripcionPromocion']) && is_string($_POST['descripcionPromocion'])
        ? trim($_POST['descripcionPromocion']) : '';
    $descuento = isset($_POST['descuentoPromocion']) && is_string($_POST['descuentoPromocion'])
        ? trim(str_replace(',', '.', $_POST['descuentoPromocion'])) : '';
    $valores = ['descripcionPromocion' => $descripcion, 'descuentoPromocion' => $descuento];

    if ($descripcion === '') {
        $errores['descripcionPromocion'] = 'Ingresá una descripción para la promoción.';
    } elseif (mb_strlen($descripcion, 'UTF-8') > 200) {
        $errores['descripcionPromocion'] = 'La descripción no puede superar los 200 caracteres.';
    }
    if ($descuento === '' || !is_numeric($descuento) || (float) $descuento < 1 || (float) $descuento > 100) {
        $errores['descuentoPromocion'] = 'Ingresá un descuento numérico entre 1 y 100.';
    }

    if ($id > 0) {
        $stmtEstado = $conexion->prepare('SELECT estadoPromocion FROM PROMOCIONES WHERE codPromocion = ? AND codAerolinea = ?');
        if (!$stmtEstado) {
            die('No se pudo verificar el estado de la promoción.');
        }
        $stmtEstado->bind_param('ii', $id, $codAerolinea);
        $stmtEstado->execute();
        $promocion = $stmtEstado->get_result()->fetch_assoc();
        if (!$promocion || $promocion['estadoPromocion'] !== 'pendiente') {
            header('Location: abm_promociones.php');
            exit();
        }
    }

    if (!$errores) {
        $descripcionGuardada = $descripcion;
        $descuentoGuardado = (float) $descuento;
        if ($id === 0) {
            $stmt = $conexion->prepare("INSERT INTO PROMOCIONES (descripcionPromocion, descuentoPromocion, codAerolinea, estadoPromocion) VALUES (?, ?, ?, 'pendiente')");
            if (!$stmt) {
                die('No se pudo preparar el alta de la promoción.');
            }
            $stmt->bind_param('sdi', $descripcionGuardada, $descuentoGuardado, $codAerolinea);
        } else {
            $stmt = $conexion->prepare("UPDATE PROMOCIONES SET descripcionPromocion = ?, descuentoPromocion = ?, estadoPromocion = 'pendiente' WHERE codPromocion = ? AND codAerolinea = ? AND estadoPromocion = 'pendiente'");
            if (!$stmt) {
                die('No se pudo preparar la modificación de la promoción.');
            }
            $stmt->bind_param('sdii', $descripcionGuardada, $descuentoGuardado, $id, $codAerolinea);
        }
        if (!$stmt->execute()) {
            die('No se pudo guardar la promoción.');
        }
        header('Location: abm_promociones.php');
        exit();
    }
}

$title = ($id > 0 ? 'Editar promoción' : 'Nueva promoción') . ' | ViajAir';
require '../../includes/header.php';
?>

<div class="row">
    <?php
    $active_page = 'promociones';
    require '../sidebar_ceo.php';
    ?>
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="/tpi/ceo/dashboard.php">Panel CEO</a></li>
                <li class="breadcrumb-item"><a href="abm_promociones.php">Promociones</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $id > 0 ? 'Editar' : 'Nueva'; ?></li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <h1 class="h5 mb-0"><?= $id > 0 ? 'Modificar Promoción' : 'Crear Promoción'; ?></h1>
                <a href="abm_promociones.php" class="btn btn-outline-light btn-sm text-white">Volver al listado</a>
            </div>
            <div class="card-body p-4">
                <?php if ($errores): ?>
                    <div class="alert alert-danger" role="alert" aria-live="assertive">
                        Revisá los campos marcados y volvé a intentar.
                    </div>
                <?php endif; ?>
                <form action="form_promocion.php<?= $id ? '?id=' . $id : ''; ?>" method="POST" class="row g-3">
                    <input type="hidden" name="id" value="<?= $id; ?>">
                    <fieldset class="row g-3 border-0 p-0 m-0">
                        <legend class="visually-hidden">Datos de la promoción</legend>
                        <div class="col-12">
                            <label for="descripcionPromocion" class="form-label">
                                Descripción
                                <span class="text-danger" aria-hidden="true">*</span>
                                <span class="visually-hidden">(campo obligatorio)</span>
                            </label>
                            <textarea id="descripcionPromocion" name="descripcionPromocion"
                                      class="form-control<?= isset($errores['descripcionPromocion']) ? ' is-invalid' : ''; ?>"
                                      maxlength="200" rows="3" required aria-required="true"
                                      aria-describedby="descripcion-ayuda<?= isset($errores['descripcionPromocion']) ? ' descripcion-error' : ''; ?>"
                                      <?= isset($errores['descripcionPromocion']) ? 'aria-invalid="true"' : ''; ?>><?= htmlspecialchars($valores['descripcionPromocion'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
                            <div id="descripcion-ayuda" class="form-text">Describí la oferta comercial, hasta 200 caracteres.</div>
                            <?php if (isset($errores['descripcionPromocion'])): ?>
                                <div id="descripcion-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['descripcionPromocion'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="descuentoPromocion" class="form-label">
                                Descuento (%)
                                <span class="text-danger" aria-hidden="true">*</span>
                                <span class="visually-hidden">(campo obligatorio)</span>
                            </label>
                            <input type="number" id="descuentoPromocion" name="descuentoPromocion"
                                   class="form-control<?= isset($errores['descuentoPromocion']) ? ' is-invalid' : ''; ?>"
                                   value="<?= htmlspecialchars($valores['descuentoPromocion'], ENT_QUOTES, 'UTF-8'); ?>"
                                   min="1" max="100" step="0.01" inputmode="decimal"
                                   required aria-required="true"
                                   aria-describedby="descuento-ayuda<?= isset($errores['descuentoPromocion']) ? ' descuento-error' : ''; ?>"
                                   <?= isset($errores['descuentoPromocion']) ? 'aria-invalid="true"' : ''; ?>>
                            <div id="descuento-ayuda" class="form-text">Ingresá un porcentaje entre 1 y 100.</div>
                            <?php if (isset($errores['descuentoPromocion'])): ?>
                                <div id="descuento-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['descuentoPromocion'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </fieldset>
                    <div class="col-12 mt-4 d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary px-5">
                            <?= $id > 0 ? 'Guardar modificaciones' : 'Crear promoción'; ?>
                        </button>
                        <a href="abm_promociones.php" class="btn btn-outline-secondary px-5">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>
