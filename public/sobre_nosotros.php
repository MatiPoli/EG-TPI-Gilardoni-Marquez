<?php
require '../includes/sesiones.php';

$pagina = 'Sobre Nosotros';
$title = $pagina . " | ViajAir";
require '../includes/header.php';
?>

<div class="container mb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sobre Nosotros</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <h1 class="mb-4 text-center text-black">Sobre Nosotros</h1>
            <div id="sobre_nosotros_contenido">
                <img class="img-fluid mb-4" src="<?= $app_url ?>/tpi/assets/img/sobre_nosotros.jpg" alt="Atardecer en un aeropuerto" title="Atardecer en el Aeropuerto">
                <div>
                    <h2>¿Quiénes somos?</h2>
                    <p>ViajAir es una empresa rosarina dedicada a la comercialización y gestión de pasajes aéreos a través de una plataforma web. Desde Rosario, trabajamos para acercar a los pasajeros la oferta de múltiples aerolíneas en un único lugar, donde pueden buscar vuelos, comparar precios, aprovechar promociones y administrar sus reservas de manera simple y segura. Nuestra plataforma está pensada para todas las personas que necesitan viajar: quienes organizan una escapada con amigos, quienes se trasladan por trabajo y quienes planifican las vacaciones en familia.</p>
                    
                    <h2>Nuestra misión</h2>
                    <p>Nuestra misión es facilitar el acceso a los viajes aéreos mediante un proceso de búsqueda y reserva rápido, claro y transparente. Buscamos conectar de manera eficiente a los pasajeros con las aerolíneas, brindando a los primeros información confiable para decidir mejor y a las segundas herramientas de autogestión para publicar sus vuelos y lanzar promociones que atraigan a más clientes.</p>
                    
                    <h2>Nuestra visión</h2>
                    <p>Aspiramos a ser una plataforma de referencia en la región para la compra de pasajes aéreos, reconocida por la confianza que genera en sus usuarios, por el valor que aporta a las aerolíneas que operan en ella y por una experiencia de uso que no deje a nadie afuera.</p>
                    
                    <h2>Compromiso con la accesibilidad</h2>
                    <p>Creemos que viajar es un derecho y que reservar un pasaje no debería depender de las capacidades físicas, sensoriales o cognitivas de cada persona, ni de su experiencia con la tecnología. Por eso diseñamos ViajAir siguiendo las pautas de accesibilidad web del W3C (WCAG) y los lineamientos de la Guía para el Desarrollo de Sitios Web del Gobierno de Chile.</p>
                </div>
                
                
            </div>
            
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>