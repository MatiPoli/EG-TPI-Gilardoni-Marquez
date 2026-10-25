<?php

require '../../includes/sesiones.php';
require '../../includes/conexion.php';
// checkRole('asset');

$pagina = 'Formulario genérico';
$title = $pagina . ' | ViajAir';
$errores = [];
$alerta = null;
$valores = [
    'nombre' => '',
    'email' => '',
    'codAerolinea' => '',
    'telefono' => '',
    'cantidad' => '',
    'fecha' => '',
    'horaSalidaVuelo' => '',
    'precioVuelo' => '',
    'descuentoPromocion' => '',
    'mensaje' => '',
    'categoria' => '',
    'contacto' => '',
    'temas' => [],
];
// Valores demostrativos: en un ABM se inicializan con los datos del registro.
$emailSoloLectura = 'usuario@ejemplo.com';
$idEjemplo = 12;
$opcionesTemas = [
    'vuelos' => 'Vuelos',
    'reservas' => 'Reservas',
    'otros' => 'Otros',
];

// Las opciones de este select se consultan en la base de datos.
$aerolineas = [];
$resultadoAerolineas = $conexion->query(
    'SELECT codAerolinea, nombreAerolinea FROM AEROLINEAS ORDER BY nombreAerolinea'
);
if ($resultadoAerolineas === false) {
    die('No se pudieron cargar las aerolíneas para el formulario.');
}
while ($aerolinea = $resultadoAerolineas->fetch_assoc()) {
    $aerolineas[(string) $aerolinea['codAerolinea']] = $aerolinea['nombreAerolinea'];
}

// Recibir, conservar y validar los datos antes de procesarlos.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $camposTexto = [
        'nombre',
        'email',
        'codAerolinea',
        'telefono',
        'cantidad',
        'fecha',
        'horaSalidaVuelo',
        'precioVuelo',
        'descuentoPromocion',
        'mensaje',
        'categoria',
        'contacto',
    ];

    foreach ($camposTexto as $campo) {
        if (isset($_POST[$campo]) && is_string($_POST[$campo])) {
            $valores[$campo] = trim($_POST[$campo]);
        }
    }

    if (isset($_POST['temas']) && is_array($_POST['temas'])) {
        $valores['temas'] = array_values(array_intersect(
            array_filter($_POST['temas'], 'is_string'),
            array_keys($opcionesTemas)
        ));
    }

    if ($valores['nombre'] === '') {
        $errores['nombre'] = 'Ingresá tu nombre.';
    } elseif (mb_strlen($valores['nombre'], 'UTF-8') > 100) {
        $errores['nombre'] = 'El nombre no puede superar los 100 caracteres.';
    }
    if ($valores['email'] === '') {
        $errores['email'] = 'Ingresá tu correo electrónico.';
    } elseif (mb_strlen($valores['email'], 'UTF-8') > 100) {
        $errores['email'] = 'El correo electrónico no puede superar los 100 caracteres.';
    } elseif (!filter_var($valores['email'], FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'Ingresá un correo electrónico válido.';
    }
    if (!array_key_exists($valores['codAerolinea'], $aerolineas)) {
        $errores['codAerolinea'] = 'Seleccioná una aerolínea válida.';
    }
    if ($valores['telefono'] === '') {
        $errores['telefono'] = 'Ingresá tu teléfono.';
    } elseif (!preg_match('/^[0-9+\s().-]{7,20}$/', $valores['telefono'])) {
        $errores['telefono'] = 'Ingresá un teléfono válido.';
    }
    if (!isset($_POST['contrasena']) || !is_string($_POST['contrasena']) || strlen($_POST['contrasena']) < 8) {
        $errores['contrasena'] = 'La contraseña debe tener al menos 8 caracteres.';
    }
    if ($valores['cantidad'] === '' || filter_var($valores['cantidad'], FILTER_VALIDATE_INT) === false
        || (int) $valores['cantidad'] < 1 || (int) $valores['cantidad'] > 10) {
        $errores['cantidad'] = 'Ingresá un número entero entre 1 y 10.';
    }

    $fechaValida = DateTime::createFromFormat('!Y-m-d', $valores['fecha']);
    if ($valores['fecha'] === '' || !$fechaValida || $fechaValida->format('Y-m-d') !== $valores['fecha']) {
        $errores['fecha'] = 'Seleccioná una fecha válida.';
    }
    $horaValida = DateTime::createFromFormat('!H:i', $valores['horaSalidaVuelo']);
    if ($valores['horaSalidaVuelo'] === '' || !$horaValida || $horaValida->format('H:i') !== $valores['horaSalidaVuelo']) {
        $errores['horaSalidaVuelo'] = 'Ingresá una hora válida.';
    }
    if (!preg_match('/^(?:\d{1,3}|\d{4,8}|\d{1,2}(?:\.\d{3}){1,2})(?:,\d{1,2})?$/', $valores['precioVuelo'])) {
        $errores['precioVuelo'] = 'Ingresá un precio válido en formato argentino, con hasta dos decimales.';
    }
    if (!preg_match('/^\d{1,3}(?:\.\d{1,2})?$/', $valores['descuentoPromocion'])
        || (float) $valores['descuentoPromocion'] > 100) {
        $errores['descuentoPromocion'] = 'Ingresá un descuento entre 0 y 100, con hasta dos decimales.';
    }
    if ($valores['mensaje'] === '') {
        $errores['mensaje'] = 'Escribí un mensaje.';
    } elseif (mb_strlen($valores['mensaje'], 'UTF-8') > 2000) {
        $errores['mensaje'] = 'El mensaje no puede superar los 2000 caracteres.';
    }
    if (!in_array($valores['categoria'], ['consulta', 'reclamo', 'sugerencia'], true)) {
        $errores['categoria'] = 'Seleccioná una categoría.';
    }
    if (!in_array($valores['contacto'], ['email', 'telefono'], true)) {
        $errores['contacto'] = 'Seleccioná una opción de contacto.';
    }
    if (count($valores['temas']) === 0) {
        $errores['temas'] = 'Seleccioná al menos un tema.';
    }

    // En formularios reales, tras procesar un POST válido guardá el mensaje en sesión,
    // redirigí con header('Location: ...') y ejecutá exit; el GET consume el mensaje flash (PRG).
    $alerta = empty($errores)
        ? ['tipo' => 'success', 'mensaje' => 'El formulario se validó correctamente.']
        : ['tipo' => 'danger', 'mensaje' => 'Revisá los campos marcados y volvé a intentar.'];
}

require '../../includes/header.php';
?>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <h1 class="mb-4"><?= htmlspecialchars($pagina, ENT_QUOTES, 'UTF-8'); ?></h1>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <?php if ($alerta !== null): ?>
                        <div class="alert alert-<?= htmlspecialchars($alerta['tipo'], ENT_QUOTES, 'UTF-8'); ?>"
                             role="<?= $alerta['tipo'] === 'success' ? 'status' : 'alert'; ?>">
                            <?= htmlspecialchars($alerta['mensaje'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <!-- En un ABM real, completá este id desde el registro y validalo también en el servidor. -->
                        <input type="hidden" name="id" value="<?= $idEjemplo; ?>">
                        <p>Los campos marcados con <span aria-hidden="true">*</span> son obligatorios.</p>

                        <!-- Campos de texto comunes -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control<?= isset($errores['nombre']) ? ' is-invalid' : ''; ?>" id="nombre" name="nombre"
                                   value="<?= htmlspecialchars($valores['nombre'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                                   maxlength="100" autocomplete="name"
                                   required aria-describedby="nombre-ayuda<?= isset($errores['nombre']) ? ' nombre-error' : ''; ?>"
                                   <?= isset($errores['nombre']) ? 'aria-invalid="true"' : ''; ?>>
                            <div id="nombre-ayuda" class="form-text">Ingresá tu nombre y apellido.</div>
                            <?php if (isset($errores['nombre'])): ?>
                                <div id="nombre-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico <span aria-hidden="true">*</span></label>
                            <input type="email" class="form-control<?= isset($errores['email']) ? ' is-invalid' : ''; ?>" id="email" name="email"
                                   value="<?= htmlspecialchars($valores['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                                   maxlength="100"
                                   autocomplete="email" required
                                   aria-describedby="email-ayuda<?= isset($errores['email']) ? ' email-error' : ''; ?>"
                                   <?= isset($errores['email']) ? 'aria-invalid="true"' : ''; ?>>
                            <div id="email-ayuda" class="form-text">Ejemplo: nombre@ejemplo.com.</div>
                            <?php if (isset($errores['email'])): ?>
                                <div id="email-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['email'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Campo de solo lectura: se muestra, pero no se envía porque no tiene name. -->
                        <div class="mb-3">
                            <label for="email-solo-lectura" class="form-label">Correo electrónico (solo lectura)</label>
                            <input type="email" class="form-control" id="email-solo-lectura"
                                   value="<?= htmlspecialchars($emailSoloLectura, ENT_QUOTES, 'UTF-8'); ?>"
                                   readonly aria-readonly="true" aria-describedby="email-solo-lectura-ayuda">
                            <div id="email-solo-lectura-ayuda" class="form-text">
                                Ejemplo de dato informativo que no se puede modificar, como en el perfil.
                            </div>
                        </div>

                        <!-- Select dinámico: reemplazá la consulta por las opciones de la entidad relacionada. -->
                        <div class="mb-3">
                            <label for="codAerolinea" class="form-label">Aerolínea <span aria-hidden="true">*</span></label>
                            <select
                                    class="form-select<?= isset($errores['codAerolinea']) ? ' is-invalid' : ''; ?>"
                                    id="codAerolinea"
                                    name="codAerolinea"
                                    required
                                    aria-describedby="aerolinea-ayuda<?= isset($errores['codAerolinea']) ? ' aerolinea-error' : ''; ?>"
                                    <?= isset($errores['codAerolinea']) ? 'aria-invalid="true"' : ''; ?>>
                                <option value=""><?= $aerolineas ? 'Elegí una aerolínea' : 'No hay aerolíneas cargadas'; ?></option>
                                <?php foreach ($aerolineas as $codigo => $nombreAerolinea): ?>
                                    <option value="<?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>"
                                        <?= $valores['codAerolinea'] === $codigo ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($nombreAerolinea, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="aerolinea-ayuda" class="form-text">
                                <?= $aerolineas
                                    ? 'Las opciones se cargan desde la tabla AEROLINEAS.'
                                    : 'Cargá una aerolínea para poder seleccionarla.'; ?>
                            </div>
                            <?php if (isset($errores['codAerolinea'])): ?>
                                <div id="aerolinea-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['codAerolinea'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono <span aria-hidden="true">*</span></label>
                            <input
                                type="tel"
                                class="form-control<?= isset($errores['telefono']) ? ' is-invalid' : ''; ?>"
                                id="telefono"
                                name="telefono"
                                   value="<?= htmlspecialchars($valores['telefono'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                                   autocomplete="tel" required
                                   aria-describedby="telefono-ayuda<?= isset($errores['telefono']) ? ' telefono-error' : ''; ?>"
                                   <?= isset($errores['telefono']) ? 'aria-invalid="true"' : ''; ?>>
                            <div id="telefono-ayuda" class="form-text">Podés incluir código de área, espacios o guiones.</div>
                            <?php if (isset($errores['telefono'])): ?>
                                <div id="telefono-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['telefono'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Ejemplos de campos para vuelos y promociones -->
                        <div class="mb-3">
                            <label for="horaSalidaVuelo" class="form-label">Hora de salida <span aria-hidden="true">*</span></label>
                            <input
                                type="time"
                                class="form-control<?= isset($errores['horaSalidaVuelo']) ? ' is-invalid' : ''; ?>"
                                id="horaSalidaVuelo"
                                name="horaSalidaVuelo"
                                   value="<?= htmlspecialchars($valores['horaSalidaVuelo'], ENT_QUOTES, 'UTF-8'); ?>"
                                   required aria-describedby="hora-ayuda<?= isset($errores['horaSalidaVuelo']) ? ' hora-error' : ''; ?>"
                                   <?= isset($errores['horaSalidaVuelo']) ? 'aria-invalid="true"' : ''; ?>>
                            <div id="hora-ayuda" class="form-text">Ejemplo de campo hora, como horaSalidaVuelo de un vuelo.</div>
                            <?php if (isset($errores['horaSalidaVuelo'])): ?>
                                <div id="hora-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['horaSalidaVuelo'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="precioVuelo" class="form-label">Precio del vuelo <span aria-hidden="true">*</span></label>
                            <input
                                type="text"
                                inputmode="decimal"
                                class="form-control<?= isset($errores['precioVuelo']) ? ' is-invalid' : ''; ?>"
                                id="precioVuelo"
                                name="precioVuelo"
                                   value="<?= htmlspecialchars($valores['precioVuelo'], ENT_QUOTES, 'UTF-8'); ?>"
                                   maxlength="13" pattern="(?:\d{1,3}|\d{4,8}|\d{1,2}(?:\.\d{3}){1,2})(?:,\d{1,2})?" required
                                   aria-describedby="precio-ayuda<?= isset($errores['precioVuelo']) ? ' precio-error' : ''; ?>"
                                   <?= isset($errores['precioVuelo']) ? 'aria-invalid="true"' : ''; ?>>
                            <div id="precio-ayuda" class="form-text">Usá punto para miles y coma para decimales; ejemplo: 1.250,50.</div>
                            <?php if (isset($errores['precioVuelo'])): ?>
                                <div id="precio-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['precioVuelo'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="descuentoPromocion" class="form-label">Descuento (%) <span aria-hidden="true">*</span></label>
                            <input
                                type="number"
                                class="form-control<?= isset($errores['descuentoPromocion']) ? ' is-invalid' : ''; ?>"
                                id="descuentoPromocion"
                                name="descuentoPromocion"
                                   value="<?= htmlspecialchars($valores['descuentoPromocion'], ENT_QUOTES, 'UTF-8'); ?>"
                                   min="0" max="100" step="0.01" required
                                   aria-describedby="descuento-ayuda<?= isset($errores['descuentoPromocion']) ? ' descuento-error' : ''; ?>"
                                   <?= isset($errores['descuentoPromocion']) ? 'aria-invalid="true"' : ''; ?>>
                            <div id="descuento-ayuda" class="form-text">Ingresá un porcentaje entre 0 y 100, con hasta dos decimales.</div>
                            <?php if (isset($errores['descuentoPromocion'])): ?>
                                <div id="descuento-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['descuentoPromocion'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Otros tipos comunes de campo: contraseña, número, fecha, texto largo y select. -->
                        <div class="mb-3">
                            <label for="contrasena" class="form-label">Contraseña <span aria-hidden="true">*</span></label>
                            <input
                                type="password"
                                class="form-control<?= isset($errores['contrasena']) ? ' is-invalid' : ''; ?>"
                                id="contrasena"
                                name="contrasena"
                                   autocomplete="new-password" required minlength="8"
                                   aria-describedby="contrasena-ayuda<?= isset($errores['contrasena']) ? ' contrasena-error' : ''; ?>"
                                   <?= isset($errores['contrasena']) ? 'aria-invalid="true"' : ''; ?>>
                            <div id="contrasena-ayuda" class="form-text">Usá al menos 8 caracteres. No se conserva si hay un error.</div>
                            <?php if (isset($errores['contrasena'])): ?>
                                <div id="contrasena-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['contrasena'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad <span aria-hidden="true">*</span></label>
                            <input
                                type="number"
                                class="form-control<?= isset($errores['cantidad']) ? ' is-invalid' : ''; ?>"
                                id="cantidad"
                                name="cantidad"
                                   value="<?= htmlspecialchars($valores['cantidad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                                   min="1" max="10" step="1" required
                                   aria-describedby="cantidad-ayuda<?= isset($errores['cantidad']) ? ' cantidad-error' : ''; ?>"
                                   <?= isset($errores['cantidad']) ? 'aria-invalid="true"' : ''; ?>>
                            <div id="cantidad-ayuda" class="form-text">Ingresá un número entero entre 1 y 10.</div>
                            <?php if (isset($errores['cantidad'])): ?>
                                <div id="cantidad-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['cantidad'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="fecha" class="form-label">Fecha <span aria-hidden="true">*</span></label>
                            <input type="date" class="form-control<?= isset($errores['fecha']) ? ' is-invalid' : ''; ?>" id="fecha" name="fecha"
                                   value="<?= htmlspecialchars($valores['fecha'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                                   required
                                   aria-describedby="fecha-ayuda<?= isset($errores['fecha']) ? ' fecha-error' : ''; ?>"
                                   <?= isset($errores['fecha']) ? 'aria-invalid="true"' : ''; ?>>
                            <div id="fecha-ayuda" class="form-text">Seleccioná una fecha.</div>
                            <?php if (isset($errores['fecha'])): ?>
                                <div id="fecha-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['fecha'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="mensaje" class="form-label">Mensaje <span aria-hidden="true">*</span></label>
                            <textarea
                                class="form-control<?= isset($errores['mensaje']) ? ' is-invalid' : ''; ?>"
                                id="mensaje"
                                name="mensaje"
                                rows="4"
                                maxlength="2000"
                                required
                                aria-describedby="mensaje-ayuda<?= isset($errores['mensaje']) ? ' mensaje-error' : ''; ?>"
                                <?= isset($errores['mensaje']) ? 'aria-invalid="true"' : ''; ?>><?php
                                echo htmlspecialchars($valores['mensaje'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            ?></textarea>
                            <div id="mensaje-ayuda" class="form-text">Contanos brevemente en qué podemos ayudarte.</div>
                            <?php if (isset($errores['mensaje'])): ?>
                                <div id="mensaje-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['mensaje'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="categoria" class="form-label">Categoría <span aria-hidden="true">*</span></label>
                            <select
                                class="form-select<?= isset($errores['categoria']) ? ' is-invalid' : ''; ?>"
                                id="categoria"
                                name="categoria"
                                required
                                    aria-describedby="categoria-ayuda<?= isset($errores['categoria']) ? ' categoria-error' : ''; ?>"
                                    <?= isset($errores['categoria']) ? 'aria-invalid="true"' : ''; ?>>
                                <option value="">Elegí una categoría</option>
                                <option value="consulta" <?= $valores['categoria'] === 'consulta' ? 'selected' : ''; ?>>Consulta</option>
                                <option value="reclamo" <?= $valores['categoria'] === 'reclamo' ? 'selected' : ''; ?>>Reclamo</option>
                                <option value="sugerencia" <?= $valores['categoria'] === 'sugerencia' ? 'selected' : ''; ?>>Sugerencia</option>
                            </select>
                            <div id="categoria-ayuda" class="form-text">Seleccioná la opción que mejor describa tu mensaje.</div>
                            <?php if (isset($errores['categoria'])): ?>
                                <div id="categoria-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['categoria'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Grupos: la ayuda y el error se asocian al fieldset, no se repiten por opción. -->
                        <fieldset class="mb-3"
                                  aria-describedby="contacto-ayuda<?= isset($errores['contacto']) ? ' contacto-error' : ''; ?>">
                            <legend class="form-label">¿Cómo preferís que te contactemos? <span aria-hidden="true">*</span></legend>
                            <div class="form-check">
                                <input
                                    class="form-check-input<?= isset($errores['contacto']) ? ' is-invalid' : ''; ?>"
                                    type="radio"
                                    id="contacto-email"
                                    name="contacto"
                                    value="email"
                                    required
                                    <?= $valores['contacto'] === 'email' ? 'checked' : ''; ?>
                                    <?= isset($errores['contacto']) ? 'aria-invalid="true"' : ''; ?>>
                                <label class="form-check-label" for="contacto-email">Correo electrónico</label>
                            </div>
                            <div class="form-check">
                                <input
                                    class="form-check-input<?= isset($errores['contacto']) ? ' is-invalid' : ''; ?>"
                                    type="radio"
                                    id="contacto-telefono"
                                    name="contacto"
                                    value="telefono"
                                    <?= $valores['contacto'] === 'telefono' ? 'checked' : ''; ?>
                                    <?= isset($errores['contacto']) ? 'aria-invalid="true"' : ''; ?>>
                                <label class="form-check-label" for="contacto-telefono">Teléfono</label>
                            </div>
                            <div id="contacto-ayuda" class="form-text">Elegí una opción.</div>
                            <?php if (isset($errores['contacto'])): ?>
                                <div id="contacto-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['contacto'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </fieldset>

                        <fieldset class="mb-4"
                                  aria-describedby="temas-ayuda<?= isset($errores['temas']) ? ' temas-error' : ''; ?>">
                            <legend class="form-label">Temas de interés <span aria-hidden="true">*</span></legend>
                            <?php foreach ($opcionesTemas as $valor => $etiqueta): ?>
                                <div class="form-check">
                                    <input
                                        class="form-check-input<?= isset($errores['temas']) ? ' is-invalid' : ''; ?>"
                                        type="checkbox"
                                        id="tema-<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); ?>"
                                        name="temas[]"
                                        value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); ?>"
                                        <?= in_array($valor, $valores['temas'], true) ? 'checked' : ''; ?>
                                        <?= isset($errores['temas']) ? 'aria-invalid="true"' : ''; ?>>
                                    <label class="form-check-label" for="tema-<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                            <div id="temas-ayuda" class="form-text">Podés seleccionar más de una opción.</div>
                            <?php if (isset($errores['temas'])): ?>
                                <div id="temas-error" class="invalid-feedback d-block">
                                    Error: <?= htmlspecialchars($errores['temas'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </fieldset>

                        <button type="submit" class="btn btn-primary">Enviar formulario</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>
