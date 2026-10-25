<?php
require '../../includes/sesiones.php';
checkRole('ceo');
require '../../includes/conexion.php';

$id = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
    : filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$id = ($id && $id > 0) ? $id : 0;

if ($id > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $conexion->prepare("SELECT * FROM VUELOS WHERE codVuelo = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    
    if ($data) {
        $origen = $data['origenVuelo'];
        $destino = $data['destinoVuelo'];
        $fechaSalida = $data['fechaSalidaVuelo'];
        $horaSalida = $data['horaSalidaVuelo'];
        $precio = $data['precioVuelo'];
        $asientosDisponibles = $data['asientosDisponibles'];
    } else {
        header("Location: abm_vuelos.php");
        exit();
    }
}

$errores = [];
$alerta = null;
$fechaMinima = date('Y-m-d');
$valores = [
    'origen' => $origen ?? '',
    'destino' => $destino ?? '',
    'fechaSalidaVuelo' => $fechaSalida ?? '',
    'horaSalidaVuelo' => $horaSalida ?? '',
    'precioVuelo' => isset($precio) ? number_format((float) $precio, 2, ',', '.') : '',
    'asientosDisponibles' => $asientosDisponibles?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $camposTexto = [
        'origen',
        'destino',
    ];

    foreach ($camposTexto as $campo) {
        if (isset($_POST[$campo]) && is_string($_POST[$campo])) {
            $valores[$campo] = trim($_POST[$campo]);
        }
    }

    if ($valores['origen'] === '') {
        $errores['origen'] = 'Ingresá la ciudad de origen.';
    } elseif (mb_strlen($valores['origen'], 'UTF-8') > 100) {
        $errores['origen'] = 'El nombre del origen no puede superar los 100 caracteres.';
    }

    if ($valores['destino'] === '') {
        $errores['destino'] = 'Ingresá la ciudad de destino.';
    } elseif (mb_strlen($valores['destino'], 'UTF-8') > 100) {
        $errores['destino'] = 'El nombre del destino no puede superar los 100 caracteres.';
    }

    $fechaValida = DateTime::createFromFormat('!Y-m-d', $valores['fechaSalidaVuelo']);
    if ($valores['fechaSalidaVuelo'] === '' || !$fechaValida || $fechaValida->format('Y-m-d') !== $valores['fechaSalidaVuelo']) {
        $errores['fechaSalidaVuelo'] = 'Seleccioná una fecha válida.';
    } elseif ($valores['fechaSalidaVuelo'] < $fechaMinima) {
        $errores['fechaSalidaVuelo'] = 'La fecha de salida no puede ser anterior a hoy.';
    }
    $horaValida = DateTime::createFromFormat('!H:i', $valores['horaSalidaVuelo']);
    if ($valores['horaSalidaVuelo'] === '' || !$horaValida || $horaValida->format('H:i') !== $valores['horaSalidaVuelo']) {
        $errores['horaSalidaVuelo'] = 'Ingresá una hora válida.';
    }

    if (!preg_match('/^(?:\d{1,3}|\d{4,8}|\d{1,2}(?:\.\d{3}){1,2})(?:,\d{1,2})?$/', $valores['precioVuelo'])) {
        $errores['precioVuelo'] = 'Ingresá un precio válido en formato argentino, con hasta dos decimales.';
    }

    if ($valores['asientosDisponibles'] === '' || filter_var($valores['asientosDisponibles'], FILTER_VALIDATE_INT) === false
        || (int) $valores['asientosDisponibles'] < 19 || (int) $valores['asientosDisponibles'] > 853) {
        $errores['asientosDisponibles'] = 'Ingresá un número entero entre 19  y 853.';
    }

    $alerta = empty($errores)
        ? ['tipo' => 'success', 'mensaje' => 'El formulario se validó correctamente.']
        : ['tipo' => 'danger', 'mensaje' => 'Revisá los campos marcados y volvé a intentar.'];

    if (!$errores) {
        $codAerolinea = (int) $_SESSION['codAerolinea'];
        $precioGuardado = str_replace(',', '.', str_replace('.', '', $valores['precioVuelo']));
        $origenGuardado = $valores['origen'];
        $destinoGuardado = $valores['destino'];
        $fechaGuardada = $valores['fechaSalidaVuelo'];
        $horaGuardada = $valores['horaSalidaVuelo'];
        $asientosGuardados = (int) $valores['asientosDisponibles'];
        if ($id === 0) {
            $stmt = $conexion->prepare('INSERT INTO VUELOS (origenVuelo, destinoVuelo, fechaSalidaVuelo, horaSalidaVuelo, precioVuelo, asientosDisponibles, codAerolinea) VALUES (?, ?, ?, ?, ?, ?, ?)');
            if (!$stmt) {
                die('No se pudo preparar el alta del vuelo.');
            }
            $stmt->bind_param('sssssii', $origenGuardado, $destinoGuardado, $fechaGuardada, $horaGuardada, $precioGuardado, $asientosGuardados, $codAerolinea);
        } else {
            $stmt = $conexion->prepare('UPDATE VUELOS SET origenVuelo = ?, destinoVuelo = ?, fechaSalidaVuelo = ?, horaSalidaVuelo = ?, precioVuelo = ?, asientosDisponibles = ? WHERE codVuelo = ? AND codAerolinea = ?');
            if (!$stmt) {
                die('No se pudo preparar la modificación del vuelo.');
            }
            $stmt->bind_param('sssssiii', $origenGuardado, $destinoGuardado, $fechaGuardada, $horaGuardada, $precioGuardado, $asientosGuardados, $id, $codAerolinea);
        }
        if (!$stmt->execute()) {
            die('No se pudo guardar el vuelo.');
        }
        header('Location: abm_vuelos.php');
        exit();
    }
}

$title = ($id ? 'Editar vuelo' : 'Nuevo vuelo') . ' | ViajAir';
require '../../includes/header.php';
?>

<div class="row">
    
    <?php 
    $active_page = 'vuelos';
    require '../sidebar_ceo.php'; 
    ?>
    
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="/tpi/ceo/dashboard.php">Panel CEO</a></li>
                <li class="breadcrumb-item"><a href="abm_vuelos.php">Gestión de Vuelos</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $id ? 'Editar' : 'Nuevo'; ?></li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <h1 class="h5 mb-0"><?= $id ? 'Modificar Vuelo' : 'Crear Vuelo'; ?></h1>
                <a href="abm_vuelos.php" class="btn btn-outline-light btn-sm text-white">Volver</a>
            </div>
            <div class="card-body p-4">
                <?php if ($alerta): ?>
                    <div class="alert alert-<?= htmlspecialchars($alerta['tipo'], ENT_QUOTES, 'UTF-8'); ?>"
                         role="alert" aria-live="assertive">
                        <?= htmlspecialchars($alerta['mensaje'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                <form action="form_vuelo.php<?= $id ? '?id=' . $id : ''; ?>" method="POST" class="row g-3">
                    <input type="hidden" name="id" value="<?= $id; ?>">
                    <div class="col-12">
                            <label for="origen" class="form-label">Origen <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                            <input type="text" class="form-control<?= isset($errores['origen']) ? ' is-invalid' : ''; ?>" id="origen" name="origen"
                                   value="<?= htmlspecialchars($valores['origen'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                                   maxlength="100"
                                   required aria-describedby="origen-ayuda<?= isset($errores['origen']) ? ' origen-error' : ''; ?>"
                                   <?= isset($errores['origen']) ? 'aria-invalid="true"' : ''; ?>>
                            <div id="origen-ayuda" class="form-text">Ingresá la ciudad de origen.</div>
                            <?php if (isset($errores['origen'])): ?>
                                <div id="origen-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['origen'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                    </div>
                    <div class="col-12">
                            <label for="destino" class="form-label">Destino <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                            <input type="text" class="form-control<?= isset($errores['destino']) ? ' is-invalid' : ''; ?>" id="destino" name="destino"
                                   value="<?= htmlspecialchars($valores['destino'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                                   maxlength="100"
                                   required aria-describedby="destino-ayuda<?= isset($errores['destino']) ? ' destino-error' : ''; ?>"
                                   <?= isset($errores['destino']) ? 'aria-invalid="true"' : ''; ?>>
                            <div id="destino-ayuda" class="form-text">Ingresá la ciudad de destino.</div>
                            <?php if (isset($errores['destino'])): ?>
                                <div id="destino-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['destino'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                    </div>
                    <div class="col-12 col-md-6">
                            <label for="fechaSalidaVuelo" class="form-label">Fecha de Salida <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                            <input type="date" class="form-control<?= isset($errores['fechaSalidaVuelo']) ? ' is-invalid' : ''; ?>" id="fechaSalidaVuelo" name="fechaSalidaVuelo"
                                value="<?= htmlspecialchars($valores['fechaSalidaVuelo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                                min="<?= htmlspecialchars($fechaMinima, ENT_QUOTES, 'UTF-8'); ?>"
                                required
                                aria-describedby="fechaSalidaVuelo-ayuda<?= isset($errores['fechaSalidaVuelo']) ? ' fechaSalidaVuelo-error' : ''; ?>"
                                <?= isset($errores['fechaSalidaVuelo']) ? 'aria-invalid="true"' : ''; ?>>
                            <div id="fechaSalidaVuelo-ayuda" class="form-text">Seleccioná la fecha de salida del vuelo.</div>
                            <?php if (isset($errores['fechaSalidaVuelo'])): ?>
                                <div id="fechaSalidaVuelo-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['fechaSalidaVuelo'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <div class="col-12 col-md-6">
                            <label for="horaSalidaVuelo" class="form-label">Horario de Salida <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                            <input
                                type="time"
                                class="form-control<?= isset($errores['horaSalidaVuelo']) ? ' is-invalid' : ''; ?>"
                                id="horaSalidaVuelo"
                                name="horaSalidaVuelo"
                                value="<?= htmlspecialchars($valores['horaSalidaVuelo'], ENT_QUOTES, 'UTF-8'); ?>"
                                required aria-describedby="hora-ayuda<?= isset($errores['horaSalidaVuelo']) ? ' hora-error' : ''; ?>"
                                <?= isset($errores['horaSalidaVuelo']) ? 'aria-invalid="true"' : ''; ?>>
                            <div id="hora-ayuda" class="form-text">Ingresá el horario de salida del vuelo.</div>
                            <?php if (isset($errores['horaSalidaVuelo'])): ?>
                                <div id="hora-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['horaSalidaVuelo'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                    </div>
                    <div class="col-12">
                            <label for="precioVuelo" class="form-label">Precio del Vuelo <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                            <input
                                type="text"
                                inputmode="decimal"
                                class="form-control<?= isset($errores['precioVuelo']) ? ' is-invalid' : ''; ?>"
                                id="precioVuelo"
                                name="precioVuelo"
                                   value="<?= htmlspecialchars($valores['precioVuelo'], ENT_QUOTES, 'UTF-8'); ?>"
                                   maxlength="13" pattern="(?:\d{1,3}|\d{4,8}|\d{1,2}(?:\.\d{3}){1,2})(?:,\d{1,2})?" required
                                   aria-describedby="precioVuelo-ayuda<?= isset($errores['precioVuelo']) ? ' precioVuelo-error' : ''; ?>"
                                   <?= isset($errores['precioVuelo']) ? 'aria-invalid="true"' : ''; ?>>
                            <div id="precioVuelo-ayuda" class="form-text">Precio por pasaje en pesos. Usá punto para miles y coma para decimales; ejemplo: 1.250,50.</div>
                            <?php if (isset($errores['precioVuelo'])): ?>
                                <div id="precioVuelo-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['precioVuelo'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label for="asientosDisponibles" class="form-label">Cantidad de Asientos Disponibles <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                            <input
                                type="number"
                                class="form-control<?= isset($errores['asientosDisponibles']) ? ' is-invalid' : ''; ?>"
                                id="asientosDisponibles"
                                name="asientosDisponibles"
                                   value="<?= htmlspecialchars($valores['asientosDisponibles'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                                   min="19" max="853" step="1" required
                                   aria-describedby="asientosDisponibles-ayuda<?= isset($errores['asientosDisponibles']) ? ' asientosDisponibles-error' : ''; ?>"
                                   <?= isset($errores['asientosDisponibles']) ? 'aria-invalid="true"' : ''; ?>>
                            <div id="asientosDisponibles-ayuda" class="form-text">Ingresá un número entero entre 19 y 853.</div>
                            <?php if (isset($errores['asientosDisponibles'])): ?>
                                <div id="asientosDisponibles-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['asientosDisponibles'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <div class="col-12 mt-4 d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary px-5"><?= $id ? 'Guardar modificaciones' : 'Guardar vuelo'; ?></button>
                        <a href="form_vuelo.php<?= $id ? '?id=' . rawurlencode((string) $id) : ''; ?>" class="btn btn-danger px-5">
                            <?= $id ? 'Descartar cambios' : 'Limpiar'; ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>