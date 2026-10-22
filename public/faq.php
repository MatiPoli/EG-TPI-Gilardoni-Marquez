<?php
require '../includes/sesiones.php';
require '../includes/conexion.php';
$pagina = 'Preguntas Frecuentes';
$title = $pagina . " | ViajAir";
require '../includes/header.php';

?>

<div class="container mt-2 mb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($pagina, ENT_QUOTES, 'UTF-8') ?></li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <h1 class="mb-4 text-center text-black">Preguntas Frecuentes</h1>
            
            <div class="accordion" id="faq">
                <h2 class="mb-2">Mi cuenta</h2>
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rta1" aria-expanded="false" aria-controls="rta1">
                            ¿Cómo me registro y cómo valido mi cuenta?
                        </button>
                    </h3>
                        <div id="rta1" class="accordion-collapse collapse hide" data-bs-parent="#faq">
                            <div class="accordion-body">
                                Ingresá a <a href="/tpi/auth/registro.php">Registrarse</a>, completá tus datos (nombre, teléfono, correo y contraseña) y enviá el formulario. Te llegará un correo con un enlace de validación. Hasta que hagas clic en ese enlace tu cuenta queda pendiente y no vas a poder iniciar sesión. Si el correo no aparece, revisá la carpeta de spam.
                            </div>
                        </div>
                </div>

                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rta2" aria-expanded="false" aria-controls="rta2">
                            ¿Qué hago si olvidé mi contraseña?
                        </button>
                    </h3>
                        <div id="rta2" class="accordion-collapse collapse hide" data-bs-parent="#faq">
                            <div class="accordion-body">
                                Accedé a <a href="/tpi/auth/recuperar_pass.php">Recuperar Contraseña</a>  e ingresá el correo con el que te registraste. Si existe una cuenta asociada, vas a recibir un enlace para definir una contraseña nueva.
                            </div>
                        </div>
                </div>

                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rta3" aria-expanded="false" aria-controls="rta3">
                            ¿Puedo buscar vuelos sin tener una cuenta?
                        </button>
                    </h3>
                        <div id="rta3" class="accordion-collapse collapse hide" data-bs-parent="#faq">
                            <div class="accordion-body">
                                <strong>Sí.</strong> Cualquier persona puede buscar vuelos y ver precios, promociones y asientos disponibles, además de leer las novedades. Para reservar necesitás tener una cuenta registrada y validada.
                            </div>
                        </div>
                </div>

                <h2 class="mb-2 mt-3">Búsqueda y Reservas</h2>
                <!-- TODO: en respuesta 4, añadir enlace a Buscar Vuelos  -->
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rta4" aria-expanded="false" aria-controls="rta4">
                            ¿Cómo busco un vuelo?
                        </button>
                    </h3>
                        <div id="rta4" class="accordion-collapse collapse hide" data-bs-parent="#faq">
                            <div class="accordion-body"> 
                                Podés usar la búsqueda rápida de la portada o la sección <a href="">Buscar Vuelos</a>. Indicá ciudad de origen, ciudad de destino y fecha. Los resultados se expresan en hora local argentina y los precios en pesos.
                            </div>
                        </div>
                </div>
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rta5" aria-expanded="false" aria-controls="rta5">
                            ¿Cómo reservo un vuelo y qué significa “Pendiente de Pago”? 
                        </button>
                    </h3>
                        <div id="rta5" class="accordion-collapse collapse hide" data-bs-parent="#faq">
                            <div class="accordion-body"> 
                                Desde el detalle del vuelo elegí la cantidad de pasajes y realizá la reserva. Al hacerlo, la reserva queda en estado “Pendiente de Pago” y ya te aseguraste tus asientos. Una vez que realizás el pago, pasa a “Confirmada” y se considera una compra.
                            </div>
                        </div>
                </div>
                
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rta6" aria-expanded="false" aria-controls="rta6">
                            ¿Dónde veo mis reservas y mis compras anteriores?
                        </button>
                    </h3>
                        <!-- TODO: Una vez creado el Historial de Compras, vincular el <a> -->
                        <div id="rta6" class="accordion-collapse collapse hide" data-bs-parent="#faq">
                            <div class="accordion-body"> 
                                En la sección <a href="/tpi/pasajero/reservas.php">Mis Reservas</a> encontrás tus reservas activas y en <a href="">Historial de Compras</a>, el detalle de las compras anteriores. En ambas podés consultar los datos de cada vuelo.
                            </div>
                        </div>
                </div>

                <h2 class="mb-2 mt-3">Pagos y Cancelaciones</h2>
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rta7" aria-expanded="false" aria-controls="rta7">
                            ¿Hasta cuándo puedo cancelar una reserva?
                        </button>
                    </h3>
                        <div id="rta7" class="accordion-collapse collapse hide" data-bs-parent="#faq">
                            <div class="accordion-body"> 
                                Podés cancelarla hasta <strong>72 horas</strong> antes de la salida del vuelo. Pasado ese plazo, el sistema ya no permite la cancelación.
                            </div>
                        </div>
                </div>

                <h2 class="mb-2 mt-3">Aerolíneas (CEO)</h2>
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rta8" aria-expanded="false" aria-controls="rta8">
                            ¿Cómo registro mi aerolínea como CEO?
                        </button>
                    </h3>
                        <!-- TODO: Una vez creado el formulario de contacto, vincular el <a> -->
                        <div id="rta8" class="accordion-collapse collapse hide" data-bs-parent="#faq">
                            <div class="accordion-body"> 
                                Usá la opción <a href="/tpi/auth/registro_ceo.php">Registro para CEO</a> dentro de la pagina de Registro e ingresá tus datos. A diferencia de un pasajero, tu cuenta debe ser autorizada por el administrador antes de poder usarse. Una vez aprobada, vas a poder gestionar los vuelos y promociones de tu aerolínea y consultar los reportes de ventas y ocupación. Recomendamos <a href="">contactarte con el administrador</a> previamente.
                            </div>
                        </div>
                </div>

            </div>
            <!-- TODO: Una vez creado el formulario de contacto, vincular el <a> -->
            <h2 class="mb-2 mt-3">No encontraste lo que buscabas?</h2>
            <p>Escribinos desde la sección de <a href="">Contacto</a> y te responderemos a la brevedad.</p>
            
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>