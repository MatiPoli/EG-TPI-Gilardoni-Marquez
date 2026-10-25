<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'ViajAir'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.2/dist/zephyr/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $app_url ?>/tpi/assets/css/styles.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= $app_url ?>/tpi/assets/img/favicon.png">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <a href="#contenido-principal" class="saltar-contenido">Saltar al contenido principal</a>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary py-2">
            <div class="container-fluid px-4">
                <a class="navbar-brand" href="<?= $app_url ?>/tpi/index.php">
                    <img src="<?= $app_url ?>/tpi/assets/img/logo.png" alt="ViajAir" height="46">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Alternar navegación">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
                    <ul class="navbar-nav mb-2 mb-lg-0 text-center text-lg-start">
                        <li class="nav-item"><a class="nav-link" href="/tpi/public/buscar_vuelos.php">Buscar Vuelos</a></li>
                        <li class="nav-item"><a class="nav-link" href="/tpi/public/novedades.php">Novedades</a></li>
                    </ul>
                    <ul class="navbar-nav align-items-center mb-2 mb-lg-0">
                        <?php 
                        if (isLoggedIn()) { 
                            $roleLogin = $_SESSION['tipoUsuario'];
                            $nameLogin = $_SESSION['nombreUsuario'];
                        ?>
                            <li class="nav-item me-3 text-white-50 d-none d-lg-block">
                                <?= explode(' ', trim($nameLogin))[0]; ?>
                            </li>
                            <?php if ($roleLogin == 'administrador'): ?>
                                <li class="nav-item"><a class="nav-link text-white fw-bold" href="/tpi/admin/dashboard.php">Administración</a></li>
                                <li class="nav-item"><a class="nav-link text-white fw-bold" href="/tpi/pasajero/perfil.php">Mi Perfil</a></li>
                            <?php elseif ($roleLogin == 'ceo'): ?>
                                <li class="nav-item"><a class="nav-link text-white fw-bold" href="/tpi/ceo/dashboard.php">Panel CEO</a></li>
                                <li class="nav-item"><a class="nav-link text-white fw-bold" href="/tpi/pasajero/perfil.php">Mi Perfil</a></li>
                            <?php else: ?>
                                <li class="nav-item"><a class="nav-link text-white fw-bold" href="/tpi/pasajero/reservas.php">Mis Reservas</a></li>
                                <li class="nav-item"><a class="nav-link text-white fw-bold" href="/tpi/pasajero/perfil.php">Mi Perfil</a></li>
                            <?php endif; ?>
                            <li class="nav-item"><a class="btn btn-sm btn-secondary ms-lg-2 mt-2 mt-lg-0" href="/tpi/auth/logout.php">Salir</a></li>
                        <?php } else { ?>
                            <li class="nav-item"><a class="btn btn-secondary w-100 w-lg-auto mb-2 mb-lg-0" href="/tpi/auth/login.php">Iniciar Sesión</a></li>
                            <li class="nav-item"><a class="btn btn-secondary w-100 w-lg-auto ms-lg-2" href="/tpi/auth/registro.php">Registrarse</a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main id="contenido-principal" class="px-4 mt-4 mb-4">