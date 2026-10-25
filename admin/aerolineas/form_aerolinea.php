<?php
require '../../includes/sesiones.php';
checkRole('administrador');
require '../../includes/conexion.php';

$id = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
    : filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$id = ($id && $id > 0) ? $id : 0;
$errores = [];
$valores = ['name' => '', 'iata' => '', 'country' => '', 'desc' => ''];

if ($id > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $conexion->prepare('SELECT nombreAerolinea, codigoIATA, descripcionAerolinea, codPais FROM AEROLINEAS WHERE codAerolinea = ?');
    if (!$stmt) {
        die('No se pudo preparar la consulta de la aerolínea.');
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    if (!$data) {
        header('Location: abm_aerolineas.php');
        exit();
    }
    $valores = ['name' => $data['nombreAerolinea'], 'iata' => $data['codigoIATA'], 'country' => $data['codPais'], 'desc' => $data['descripcionAerolinea']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($valores) as $campo) {
        $valor = filter_input(INPUT_POST, $campo, FILTER_UNSAFE_RAW);
        $valores[$campo] = is_string($valor) ? trim($valor) : '';
    }
    $valores['iata'] = strtoupper($valores['iata']);
    $valores['country'] = strtoupper($valores['country']);
    if ($valores['name'] === '' || mb_strlen($valores['name'], 'UTF-8') > 100) {
        $errores['name'] = 'Ingresá un nombre de hasta 100 caracteres.';
    }
    if (!preg_match('/^[A-Z]{3}$/', $valores['iata'])) {
        $errores['iata'] = 'Ingresá un código IATA de 3 letras.';
    }
    if (!preg_match('/^[A-Z]{2,3}$/', $valores['country'])) {
        $errores['country'] = 'Ingresá un código de país de 2 o 3 letras.';
    }
    if ($valores['desc'] === '' || mb_strlen($valores['desc'], 'UTF-8') > 200) {
        $errores['desc'] = 'Ingresá una descripción de hasta 200 caracteres.';
    }

    if (!$errores) {
        $nombre = $valores['name'];
        $iata = $valores['iata'];
        $descripcion = $valores['desc'];
        $pais = $valores['country'];
        if ($id === 0) {
            $stmt = $conexion->prepare('INSERT INTO AEROLINEAS (nombreAerolinea, codigoIATA, descripcionAerolinea, codPais) VALUES (?, ?, ?, ?)');
            if (!$stmt) {
                die('No se pudo preparar el registro de la aerolínea.');
            }
            $stmt->bind_param('ssss', $nombre, $iata, $descripcion, $pais);
        } else {
            $stmt = $conexion->prepare('UPDATE AEROLINEAS SET nombreAerolinea = ?, codigoIATA = ?, descripcionAerolinea = ?, codPais = ? WHERE codAerolinea = ?');
            if (!$stmt) {
                die('No se pudo preparar la actualización de la aerolínea.');
            }
            $stmt->bind_param('ssssi', $nombre, $iata, $descripcion, $pais, $id);
        }
        if (!$stmt->execute()) {
            die('No se pudo guardar la aerolínea.');
        }
        header('Location: abm_aerolineas.php');
        exit();
    }
}

$title = ($id ? 'Editar aerolínea' : 'Nueva aerolínea') . ' | ViajAir';
require '../../includes/header.php';
?>
<div class="row">
    <?php $active_page = 'aerolineas'; require '../sidebar_admin.php'; ?>
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="/tpi/admin/dashboard.php">Administración</a></li>
            <li class="breadcrumb-item"><a href="abm_aerolineas.php">Aerolíneas</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $id ? 'Editar' : 'Nueva'; ?></li>
        </ol></nav>
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h1 class="h5 mb-0"><?= $id ? 'Modificar aerolínea' : 'Registrar nueva aerolínea'; ?></h1>
                <a href="abm_aerolineas.php" class="btn btn-outline-light btn-sm">Volver al listado</a>
            </div>
            <div class="card-body p-4">
                <?php if ($errores): ?><div class="alert alert-danger" role="alert" aria-live="assertive">Revisá los campos marcados y volvé a intentar.</div><?php endif; ?>
                <form action="form_aerolinea.php<?= $id ? '?id=' . $id : ''; ?>" method="POST" class="row g-3">
                    <input type="hidden" name="id" value="<?= $id; ?>">
                    <fieldset class="row g-3 border-0 p-0 m-0">
                        <legend class="visually-hidden">Datos de la aerolínea</legend>
                        <?php
                        $campos = [
                            'name' => ['label' => 'Nombre de la aerolínea', 'type' => 'text', 'maxlength' => 100, 'class' => 'col-md-8'],
                            'iata' => ['label' => 'Código IATA', 'type' => 'text', 'maxlength' => 3, 'class' => 'col-md-4'],
                            'country' => ['label' => 'Código del país', 'type' => 'text', 'maxlength' => 3, 'class' => 'col-md-4'],
                        ];
                        foreach ($campos as $campo => $config):
                        ?>
                            <div class="<?= $config['class']; ?>">
                                <label for="<?= $campo; ?>" class="form-label"><?= $config['label']; ?> <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                                <input type="<?= $config['type']; ?>" id="<?= $campo; ?>" name="<?= $campo; ?>" class="form-control<?= isset($errores[$campo]) ? ' is-invalid' : ''; ?>" value="<?= htmlspecialchars($valores[$campo], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" maxlength="<?= $config['maxlength']; ?>" required aria-required="true" aria-describedby="<?= $campo; ?>-ayuda<?= isset($errores[$campo]) ? ' ' . $campo . '-error' : ''; ?>" <?= isset($errores[$campo]) ? 'aria-invalid="true"' : ''; ?>>
                                <div id="<?= $campo; ?>-ayuda" class="form-text"><?= $campo === 'iata' ? 'Tres letras identificadoras.' : ($campo === 'country' ? 'Código de país de 2 o 3 letras.' : 'Hasta 100 caracteres.'); ?></div>
                                <?php if (isset($errores[$campo])): ?><div id="<?= $campo; ?>-error" class="invalid-feedback d-block"><?= htmlspecialchars($errores[$campo], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div class="col-12">
                            <label for="desc" class="form-label">Descripción <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                            <textarea id="desc" name="desc" class="form-control<?= isset($errores['desc']) ? ' is-invalid' : ''; ?>" rows="3" maxlength="200" required aria-required="true" aria-describedby="desc-ayuda<?= isset($errores['desc']) ? ' desc-error' : ''; ?>" <?= isset($errores['desc']) ? 'aria-invalid="true"' : ''; ?>><?= htmlspecialchars($valores['desc'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
                            <div id="desc-ayuda" class="form-text">Hasta 200 caracteres.</div>
                            <?php if (isset($errores['desc'])): ?><div id="desc-error" class="invalid-feedback d-block"><?= htmlspecialchars($errores['desc'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                    </fieldset>
                    <div class="col-12 d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-primary"><?= $id ? 'Guardar modificaciones' : 'Crear aerolínea'; ?></button>
                        <a href="abm_aerolineas.php" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require '../../includes/footer.php'; ?>
