</main>
    <footer class="bg-dark text-white text-center py-3 px-4 mt-auto">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8 text-md-start mb-2 mb-md-0">
                    <a href="/tpi/index.php" class="text-white text-decoration-none small me-3">Inicio</a>
                    <a href="/tpi/public/buscar_vuelos.php" class="text-white text-decoration-none small me-3">Buscar Vuelos</a>
                    <a href="/tpi/public/novedades.php" class="text-white text-decoration-none small me-3">Novedades</a>
                    <a href="/tpi/pasajero/perfil.php" class="text-white text-decoration-none small me-3">Mi Perfil</a>
                    <a href="/tpi/pasajero/reservas.php" class="text-white text-decoration-none small me-3">Mis Reservas</a>
                    <a href="/tpi/pasajero/historial.php" class="text-white text-decoration-none small me-3">Historial de Compras</a>
                    <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                        <?php if ($_SESSION['tipoUsuario'] == 'administrador'): ?>
                            <a href="/tpi/admin/dashboard.php" class="text-white text-decoration-none small me-3">Panel de Administración</a>
                            <a href="/tpi/admin/aerolineas/abm_aerolineas.php" class="text-white text-decoration-none small me-3">Gestión de Aerolíneas</a>
                            <a href="/tpi/admin/auditoria.php" class="text-white text-decoration-none small me-3">Auditar Promociones</a>
                            <a href="/tpi/admin/novedades/abm_novedades.php" class="text-white text-decoration-none small me-3">Gestión de Novedades</a>
                            <a href="/tpi/admin/reportes.php" class="text-white text-decoration-none small me-3">Reportes</a>
                        <?php elseif ($_SESSION['tipoUsuario'] == 'ceo'): ?>
                            <a href="/tpi/ceo/dashboard.php" class="text-white text-decoration-none small me-3">Panel CEO</a>
                            <a href="/tpi/ceo/vuelos/abm_vuelos.php" class="text-white text-decoration-none small me-3">Gestión de Vuelos</a>
                            <a href="/tpi/ceo/reporte_ventas.php" class="text-white text-decoration-none small me-3">Reporte de Ventas</a>
                            <a href="/tpi/ceo/reporte_ocupacion.php" class="text-white text-decoration-none small me-3">Reporte de Ocupación</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="/tpi/auth/login.php" class="text-white text-decoration-none small me-3">Iniciar Sesión</a>
                        <a href="/tpi/auth/registro.php" class="text-white text-decoration-none small me-3">Registrarse</a>
                    <?php endif; ?>
                </div>

                <div class="col-md-4 text-md-end">
                    <a href="/tpi/public/sobre_nosotros.php" class="text-white-50 text-decoration-none small ms-3">Sobre Nosotros</a>
                    <a href="/tpi/public/faq.php" class="text-white-50 text-decoration-none small ms-3">Preguntas Frecuentes</a>
                    <a href="/tpi/public/contacto.php" class="text-white-50 text-decoration-none small ms-3">Contacto</a>
                </div>

            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>