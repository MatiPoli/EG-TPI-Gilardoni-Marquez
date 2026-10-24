<?php

require '../../includes/sesiones.php';
// checkRole('asset');

$pagina = 'Formulario genérico';
$title = $pagina . ' | ViajAir';
$errores = [];
$alerta = null;
$valores = [
    'nombre' => '',
    'email' => '',
    'telefono' => '',
    'cantidad' => '',
    'fecha' => '',
    'mensaje' => '',
    'categoria' => '',
    'contacto' => '',
    'temas' => [],
];
$opcionesTemas = [
    'vuelos' => 'Vuelos',
    'reservas' => 'Reservas',
    'otros' => 'Otros',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['nombre', 'email', 'telefono', 'cantidad', 'fecha', 'mensaje', 'categoria', 'contacto'] as $campo) {
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
    }
    if ($valores['email'] === '') {
        $errores['email'] = 'Ingresá tu correo electrónico.';
    } elseif (!filter_var($valores['email'], FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'Ingresá un correo electrónico válido.';
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
    if ($valores['mensaje'] === '') {
        $errores['mensaje'] = 'Escribí un mensaje.';
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

    $alerta = empty($errores)
        ? ['tipo' => 'success', 'mensaje' => 'El formulario se validó correctamente.']
        : ['tipo' => 'danger', 'mensaje' => 'Revisá los campos marcados y volvé a intentar.'];
}

require '../../includes/header.php';
?>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
            <h1 class="mb-4"><?= htmlspecialchars($pagina, ENT_QUOTES, 'UTF-8'); ?></h1>

            <?php if ($alerta !== null): ?>
                <div class="alert alert-<?= htmlspecialchars($alerta['tipo'], ENT_QUOTES, 'UTF-8'); ?>"
                     role="<?= $alerta['tipo'] === 'success' ? 'status' : 'alert'; ?>">
                    <?= htmlspecialchars($alerta['mensaje'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <p>Los campos marcados con <span aria-hidden="true">*</span> son obligatorios.</p>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre <span aria-hidden="true">*</span></label>
                    <input type="text" class="form-control" id="nombre" name="nombre"
                           value="<?= htmlspecialchars($valores['nombre'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                           required aria-describedby="nombre-ayuda<?= isset($errores['nombre']) ? ' nombre-error' : ''; ?>"
                           <?= isset($errores['nombre']) ? 'aria-invalid="true"' : ''; ?>>
                    <div id="nombre-ayuda" class="form-text">Ingresá tu nombre y apellido.</div>
                    <?php if (isset($errores['nombre'])): ?>
                        <div id="nombre-error" class="invalid-feedback d-block">
                            <?= htmlspecialchars($errores['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico <span aria-hidden="true">*</span></label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= htmlspecialchars($valores['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                           autocomplete="email" required
                           aria-describedby="email-ayuda<?= isset($errores['email']) ? ' email-error' : ''; ?>"
                           <?= isset($errores['email']) ? 'aria-invalid="true"' : ''; ?>>
                    <div id="email-ayuda" class="form-text">Ejemplo: nombre@ejemplo.com.</div>
                    <?php if (isset($errores['email'])): ?>
                        <div id="email-error" class="invalid-feedback d-block">
                            <?= htmlspecialchars($errores['email'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono <span aria-hidden="true">*</span></label>
                    <input type="tel" class="form-control" id="telefono" name="telefono"
                           value="<?= htmlspecialchars($valores['telefono'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                           autocomplete="tel" required
                           aria-describedby="telefono-ayuda<?= isset($errores['telefono']) ? ' telefono-error' : ''; ?>"
                           <?= isset($errores['telefono']) ? 'aria-invalid="true"' : ''; ?>>
                    <div id="telefono-ayuda" class="form-text">Podés incluir código de área, espacios o guiones.</div>
                    <?php if (isset($errores['telefono'])): ?>
                        <div id="telefono-error" class="invalid-feedback d-block">
                            <?= htmlspecialchars($errores['telefono'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="contrasena" class="form-label">Contraseña <span aria-hidden="true">*</span></label>
                    <input type="password" class="form-control" id="contrasena" name="contrasena"
                           autocomplete="new-password" required minlength="8"
                           aria-describedby="contrasena-ayuda<?= isset($errores['contrasena']) ? ' contrasena-error' : ''; ?>"
                           <?= isset($errores['contrasena']) ? 'aria-invalid="true"' : ''; ?>>
                    <div id="contrasena-ayuda" class="form-text">Usá al menos 8 caracteres. No se conserva si hay un error.</div>
                    <?php if (isset($errores['contrasena'])): ?>
                        <div id="contrasena-error" class="invalid-feedback d-block">
                            <?= htmlspecialchars($errores['contrasena'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="cantidad" class="form-label">Cantidad <span aria-hidden="true">*</span></label>
                    <input type="number" class="form-control" id="cantidad" name="cantidad"
                           value="<?= htmlspecialchars($valores['cantidad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                           min="1" max="10" step="1" required
                           aria-describedby="cantidad-ayuda<?= isset($errores['cantidad']) ? ' cantidad-error' : ''; ?>"
                           <?= isset($errores['cantidad']) ? 'aria-invalid="true"' : ''; ?>>
                    <div id="cantidad-ayuda" class="form-text">Ingresá un número entero entre 1 y 10.</div>
                    <?php if (isset($errores['cantidad'])): ?>
                        <div id="cantidad-error" class="invalid-feedback d-block">
                            <?= htmlspecialchars($errores['cantidad'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="fecha" class="form-label">Fecha <span aria-hidden="true">*</span></label>
                    <input type="date" class="form-control" id="fecha" name="fecha"
                           value="<?= htmlspecialchars($valores['fecha'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                           required
                           aria-describedby="fecha-ayuda<?= isset($errores['fecha']) ? ' fecha-error' : ''; ?>"
                           <?= isset($errores['fecha']) ? 'aria-invalid="true"' : ''; ?>>
                    <div id="fecha-ayuda" class="form-text">Seleccioná una fecha.</div>
                    <?php if (isset($errores['fecha'])): ?>
                        <div id="fecha-error" class="invalid-feedback d-block">
                            <?= htmlspecialchars($errores['fecha'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="mensaje" class="form-label">Mensaje <span aria-hidden="true">*</span></label>
                    <textarea class="form-control" id="mensaje" name="mensaje" rows="4" required
                              aria-describedby="mensaje-ayuda<?= isset($errores['mensaje']) ? ' mensaje-error' : ''; ?>"
                              <?= isset($errores['mensaje']) ? 'aria-invalid="true"' : ''; ?>><?= htmlspecialchars($valores['mensaje'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
                    <div id="mensaje-ayuda" class="form-text">Contanos brevemente en qué podemos ayudarte.</div>
                    <?php if (isset($errores['mensaje'])): ?>
                        <div id="mensaje-error" class="invalid-feedback d-block">
                            <?= htmlspecialchars($errores['mensaje'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="categoria" class="form-label">Categoría <span aria-hidden="true">*</span></label>
                    <select class="form-select" id="categoria" name="categoria" required
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
                            <?= htmlspecialchars($errores['categoria'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <fieldset class="mb-3"
                          aria-describedby="contacto-ayuda<?= isset($errores['contacto']) ? ' contacto-error' : ''; ?>">
                    <legend class="form-label">¿Cómo preferís que te contactemos? <span aria-hidden="true">*</span></legend>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="contacto-email" name="contacto"
                               value="email" required
                               aria-describedby="contacto-ayuda<?= isset($errores['contacto']) ? ' contacto-error' : ''; ?>"
                               <?= $valores['contacto'] === 'email' ? 'checked' : ''; ?>
                               <?= isset($errores['contacto']) ? 'aria-invalid="true"' : ''; ?>>
                        <label class="form-check-label" for="contacto-email">Correo electrónico</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="contacto-telefono" name="contacto"
                               value="telefono"
                               aria-describedby="contacto-ayuda<?= isset($errores['contacto']) ? ' contacto-error' : ''; ?>"
                               <?= $valores['contacto'] === 'telefono' ? 'checked' : ''; ?>
                               <?= isset($errores['contacto']) ? 'aria-invalid="true"' : ''; ?>>
                        <label class="form-check-label" for="contacto-telefono">Teléfono</label>
                    </div>
                    <div id="contacto-ayuda" class="form-text">Elegí una opción.</div>
                    <?php if (isset($errores['contacto'])): ?>
                        <div id="contacto-error" class="invalid-feedback d-block">
                            <?= htmlspecialchars($errores['contacto'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                </fieldset>

                <fieldset class="mb-4"
                          aria-describedby="temas-ayuda<?= isset($errores['temas']) ? ' temas-error' : ''; ?>">
                    <legend class="form-label">Temas de interés <span aria-hidden="true">*</span></legend>
                    <?php foreach ($opcionesTemas as $valor => $etiqueta): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="tema-<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); ?>"
                                   name="temas[]" value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); ?>"
                                   aria-required="true"
                                   aria-describedby="temas-ayuda<?= isset($errores['temas']) ? ' temas-error' : ''; ?>"
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
                            <?= htmlspecialchars($errores['temas'], ENT_QUOTES, 'UTF-8'); ?>
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
