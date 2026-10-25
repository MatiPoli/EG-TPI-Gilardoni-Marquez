<?php
require '../../includes/sesiones.php';
checkRole('administrador');
require '../../includes/conexion.php';

$id = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
    : filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$id = ($id && $id > 0) ? $id : 0;
$errores = [];
$valores = ['titulo' => '', 'text' => '', 'pub_date' => date('Y-m-d'), 'exp_date' => ''];
$hoy = date('Y-m-d');

if ($id > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $conexion->prepare('SELECT tituloNovedad, textoNovedad, fechaPublicacionNovedad, fechaExpiracionNovedad FROM NOVEDADES WHERE codNovedad = ?');
    if (!$stmt) {
        die('No se pudo preparar la consulta de la novedad.');
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    if (!$data) {
        header('Location: abm_novedades.php');
        exit();
    }
    $valores = ['titulo' => $data['tituloNovedad'] ?? '', 'text' => $data['textoNovedad'], 'pub_date' => $data['fechaPublicacionNovedad'], 'exp_date' => $data['fechaExpiracionNovedad']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($valores) as $campo) {
        $valor = filter_input(INPUT_POST, $campo, FILTER_UNSAFE_RAW);
        $valores[$campo] = is_string($valor) ? trim($valor) : '';
    }
    $fechaPublicacion = DateTime::createFromFormat('!Y-m-d', $valores['pub_date']);
    $fechaExpiracion = DateTime::createFromFormat('!Y-m-d', $valores['exp_date']);
    $hoyDate = new DateTime('today');

    if ($valores['titulo'] === '' || mb_strlen($valores['titulo'], 'UTF-8') > 150) {
        $errores['titulo'] = 'Ingresá un título de hasta 150 caracteres.';
    }
    if ($valores['text'] === '' || mb_strlen($valores['text'], 'UTF-8') > 200) {
        $errores['text'] = 'Ingresá un texto de hasta 200 caracteres.';
    }
    if (!$fechaPublicacion || $fechaPublicacion->format('Y-m-d') !== $valores['pub_date']) {
        $errores['pub_date'] = 'Ingresá una fecha de publicación válida.';
    } elseif (!$id && $fechaPublicacion < $hoyDate) {
        $errores['pub_date'] = 'La fecha de publicación no puede ser anterior a hoy.';
    }
    if (!$fechaExpiracion || $fechaExpiracion->format('Y-m-d') !== $valores['exp_date']) {
        $errores['exp_date'] = 'Ingresá una fecha de expiración válida.';
    } elseif ($fechaPublicacion && $fechaExpiracion <= $fechaPublicacion) {
        $errores['exp_date'] = 'La fecha de expiración debe ser posterior a la publicación.';
    }

    if (!$errores) {
        $titulo = $valores['titulo'];
        $texto = $valores['text'];
        $publicacion = $valores['pub_date'];
        $expiracion = $valores['exp_date'];
        if ($id === 0) {
            $stmt = $conexion->prepare('INSERT INTO NOVEDADES (tituloNovedad, textoNovedad, fechaPublicacionNovedad, fechaExpiracionNovedad) VALUES (?, ?, ?, ?)');
            if (!$stmt) {
                die('No se pudo preparar el alta de la novedad.');
            }
            $stmt->bind_param('ssss', $titulo, $texto, $publicacion, $expiracion);
        } else {
            $stmt = $conexion->prepare('UPDATE NOVEDADES SET tituloNovedad = ?, textoNovedad = ?, fechaPublicacionNovedad = ?, fechaExpiracionNovedad = ? WHERE codNovedad = ?');
            if (!$stmt) {
                die('No se pudo preparar la modificación de la novedad.');
            }
            $stmt->bind_param('ssssi', $titulo, $texto, $publicacion, $expiracion, $id);
        }
        if (!$stmt->execute()) {
            die('No se pudo guardar la novedad.');
        }
        header('Location: abm_novedades.php');
        exit();
    }
}

$title = ($id ? 'Editar novedad' : 'Nueva novedad') . ' | ViajAir';
require '../../includes/header.php';
?>
<div class="row">
    <?php $active_page = 'novedades'; require '../sidebar_admin.php'; ?>
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li><li class="breadcrumb-item"><a href="/tpi/admin/dashboard.php">Administración</a></li><li class="breadcrumb-item"><a href="abm_novedades.php">Novedades</a></li><li class="breadcrumb-item active" aria-current="page"><?= $id ? 'Editar' : 'Nueva'; ?></li>
        </ol></nav>
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h1 class="h5 mb-0"><?= $id ? 'Modificar novedad' : 'Registrar nueva novedad'; ?></h1>
                <a href="abm_novedades.php" class="btn btn-light btn-sm fw-bold">Volver al listado</a>
            </div>
            <div class="card-body p-4">
                <?php if ($errores): ?><div class="alert alert-danger" role="alert" aria-live="assertive">Revisá los campos marcados y volvé a intentar.</div><?php endif; ?>
                <form action="form_novedad.php<?= $id ? '?id=' . $id : ''; ?>" method="POST" class="row g-3">
                    <input type="hidden" name="id" value="<?= $id; ?>">
                    <fieldset class="row g-3 border-0 p-0 m-0"><legend class="visually-hidden">Datos de la novedad</legend>
                        <div class="col-12">
                            <label for="titulo" class="form-label">Título <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                            <input type="text" id="titulo" name="titulo" class="form-control<?= isset($errores['titulo']) ? ' is-invalid' : ''; ?>" value="<?= htmlspecialchars($valores['titulo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" maxlength="150" required aria-describedby="titulo-ayuda<?= isset($errores['titulo']) ? ' titulo-error' : ''; ?>" <?= isset($errores['titulo']) ? 'aria-invalid="true"' : ''; ?>>
                            <div id="titulo-ayuda" class="form-text">Un título breve para la novedad.</div>
                            <?php if (isset($errores['titulo'])): ?><div id="titulo-error" class="invalid-feedback d-block"><?= htmlspecialchars($errores['titulo'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label for="text" class="form-label">Texto de la novedad <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                            <textarea id="text" name="text" class="form-control<?= isset($errores['text']) ? ' is-invalid' : ''; ?>" rows="4" maxlength="200" required aria-describedby="texto-ayuda<?= isset($errores['text']) ? ' texto-error' : ''; ?>" <?= isset($errores['text']) ? 'aria-invalid="true"' : ''; ?>><?= htmlspecialchars($valores['text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
                            <div id="texto-ayuda" class="form-text">Hasta 200 caracteres.</div>
                            <?php if (isset($errores['text'])): ?><div id="texto-error" class="invalid-feedback d-block"><?= htmlspecialchars($errores['text'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="pub_date" class="form-label">Fecha de publicación <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                            <input type="date" id="pub_date" name="pub_date" class="form-control<?= isset($errores['pub_date']) ? ' is-invalid' : ''; ?>" value="<?= htmlspecialchars($valores['pub_date'], ENT_QUOTES, 'UTF-8'); ?>" min="<?= $id ? '' : $hoy; ?>" required aria-describedby="pub-ayuda<?= isset($errores['pub_date']) ? ' pub-error' : ''; ?>">
                            <div id="pub-ayuda" class="form-text">No puede ser anterior a hoy.</div>
                            <?php if (isset($errores['pub_date'])): ?><div id="pub-error" class="invalid-feedback d-block"><?= htmlspecialchars($errores['pub_date'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="exp_date" class="form-label">Fecha de expiración <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                            <input type="date" id="exp_date" name="exp_date" class="form-control<?= isset($errores['exp_date']) ? ' is-invalid' : ''; ?>" value="<?= htmlspecialchars($valores['exp_date'], ENT_QUOTES, 'UTF-8'); ?>" min="<?= $id ? '' : date('Y-m-d', strtotime('+1 day')); ?>" required aria-describedby="exp-ayuda<?= isset($errores['exp_date']) ? ' exp-error' : ''; ?>">
                            <div id="exp-ayuda" class="form-text">Debe ser posterior a la publicación.</div>
                            <?php if (isset($errores['exp_date'])): ?><div id="exp-error" class="invalid-feedback d-block"><?= htmlspecialchars($errores['exp_date'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                    </fieldset>
                    <div class="col-12 d-flex justify-content-between mt-4"><button type="submit" class="btn btn-primary"><?= $id ? 'Guardar modificaciones' : 'Crear novedad'; ?></button><a href="abm_novedades.php" class="btn btn-outline-secondary">Cancelar</a></div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require '../../includes/footer.php'; ?>
