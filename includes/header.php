<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ViajAir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/tpi/assets/css/styles.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
            <div class="container-fluid px-4">
                <a class="navbar-brand fw-bold" href="/tpi/index.php">✈️ ViajAir</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item"><a class="nav-link" href="/tpi/public/buscar_vuelos.php">Buscar Vuelos</a></li>
                        <li class="nav-item"><a class="nav-link" href="/tpi/public/novedades.php">Novedades</a></li>
                    </ul>
                    <ul class="navbar-nav align-items-center">
                        <?php 
                        if (function_exists('isLoggedIn') && isLoggedIn()) { 
                            $roleLogin = $_SESSION['tipoUsuario'];
                            $nameLogin = $_SESSION['nombreUsuario'];
                        ?>
                            <li class="nav-item me-3 text-white-50 d-none d-lg-block">
                                <?php echo explode(' ', trim($nameLogin))[0]; ?>
                            </li>
                            <?php if ($roleLogin == 'administrador'): ?>
                                <li class="nav-item"><a class="nav-link text-white fw-bold" href="/tpi/admin/dashboard.php">Panel Admin</a></li>
                            <?php elseif ($roleLogin == 'ceo'): ?>
                                <li class="nav-item"><a class="nav-link text-white fw-bold" href="/tpi/ceo/dashboard.php">Panel CEO</a></li>
                            <?php else: ?>
                                <li class="nav-item"><a class="nav-link text-white fw-bold" href="/tpi/pasajero/reservas.php">Mis Reservas</a></li>
                                <li class="nav-item"><a class="nav-link text-white fw-bold" href="/tpi/pasajero/perfil.php">Mi Perfil</a></li>
                            <?php endif; ?>
                            <li class="nav-item"><a class="btn btn-sm btn-outline-light ms-2" href="/tpi/auth/logout.php">Salir</a></li>
                        <?php } else { ?>
                            <li class="nav-item"><a class="nav-link" href="/tpi/auth/login.php">Iniciar Sesión</a></li>
                            <li class="nav-item"><a class="btn btn-outline-light ms-2" href="/tpi/auth/registro.php">Registrarse</a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main class="container-fluid px-4 mt-4 flex-grow-1">