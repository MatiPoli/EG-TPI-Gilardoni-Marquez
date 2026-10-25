CREATE DATABASE IF NOT EXISTS aerolinea_bd DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE aerolinea_bd;

CREATE TABLE USUARIOS (
    codUsuario INT AUTO_INCREMENT PRIMARY KEY,
    nombreUsuario VARCHAR(100) NOT NULL,
    claveUsuario VARCHAR(8) NOT NULL,
    tipoUsuario ENUM('administrador', 'ceo', 'usuario') NOT NULL,
    emailUsuario VARCHAR(100) NOT NULL UNIQUE,
    telefonoUsuario VARCHAR(20) NOT NULL
);

CREATE TABLE AEROLINEAS (
    codAerolinea INT AUTO_INCREMENT PRIMARY KEY,
    nombreAerolinea VARCHAR(100) NOT NULL,
    codigoIATA VARCHAR(3) NOT NULL,
    descripcionAerolinea VARCHAR(200) NOT NULL,
    codPais VARCHAR(3) NOT NULL
);

CREATE TABLE VUELOS (
    codVuelo INT AUTO_INCREMENT PRIMARY KEY,
    codAerolinea INT NOT NULL,
    origenVuelo VARCHAR(50) NOT NULL,
    destinoVuelo VARCHAR(50) NOT NULL,
    fechaSalidaVuelo DATE NOT NULL,
    horaSalidaVuelo TIME NOT NULL,
    precioVuelo DECIMAL(10,2) NOT NULL,
    asientosDisponibles INT NOT NULL,
    FOREIGN KEY (codAerolinea) REFERENCES AEROLINEAS(codAerolinea) ON DELETE CASCADE
);

CREATE TABLE PROMOCIONES (
    codPromocion INT AUTO_INCREMENT PRIMARY KEY,
    descripcionPromocion VARCHAR(200) NOT NULL,
    descuentoPromocion DECIMAL(5,2) NOT NULL,
    codAerolinea INT NOT NULL,
    estadoPromocion ENUM('pendiente', 'aprobada', 'denegada') DEFAULT 'pendiente',
    FOREIGN KEY (codAerolinea) REFERENCES AEROLINEAS(codAerolinea) ON DELETE CASCADE
);

CREATE TABLE NOVEDADES (
    codNovedad INT AUTO_INCREMENT PRIMARY KEY,
    textoNovedad VARCHAR(200) NOT NULL,
    fechaPublicacionNovedad DATE NOT NULL,
    fechaExpiracionNovedad DATE NOT NULL
);

CREATE TABLE RESERVAS (
    codReserva INT AUTO_INCREMENT PRIMARY KEY,
    codUsuario INT NOT NULL,
    codVuelo INT NOT NULL,
    fechaReserva DATE NOT NULL,
    estadoReserva ENUM('pendiente de pago', 'confirmada', 'cancelada') DEFAULT 'pendiente de pago',
    FOREIGN KEY (codUsuario) REFERENCES USUARIOS(codUsuario) ON DELETE CASCADE,
    FOREIGN KEY (codVuelo) REFERENCES VUELOS(codVuelo) ON DELETE CASCADE
);

ALTER TABLE USUARIOS ADD estadoUsuario ENUM('pendiente', 'activo', 'suspendido') NOT NULL DEFAULT 'pendiente' AFTER tipoUsuario;
ALTER TABLE USUARIOS ADD fechaRegistro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER telefonoUsuario;
ALTER TABLE USUARIOS ADD tokenRecuperacion VARCHAR(100) NULL AFTER estadoUsuario;
ALTER TABLE USUARIOS MODIFY claveUsuario VARCHAR(32) NOT NULL;
ALTER TABLE USUARIOS ADD codAerolinea INT NULL AFTER tipoUsuario;
ALTER TABLE USUARIOS ADD FOREIGN KEY (codAerolinea) REFERENCES AEROLINEAS(codAerolinea) ON DELETE SET NULL;
ALTER TABLE NOVEDADES ADD COLUMN tituloNovedad VARCHAR(150) NULL AFTER codNovedad;
ALTER TABLE RESERVAS ADD COLUMN cantidadPasajes INT NOT NULL DEFAULT 1 AFTER codVuelo;
ALTER TABLE RESERVAS ADD COLUMN precioTotal DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER fechaReserva;

INSERT INTO AEROLINEAS
    (nombreAerolinea, codigoIATA, descripcionAerolinea, codPais)
VALUES
    ('Aerolíneas Argentinas', 'ARG', 'Aerolínea de bandera argentina.', 'AR'),
    ('LATAM Airlines', 'LAN', 'Aerolínea latinoamericana.', 'CL'),
    ('Iberia', 'IBE', 'Aerolínea española con destinos internacionales.', 'ES');

INSERT INTO USUARIOS
    (nombreUsuario, claveUsuario, tipoUsuario, estadoUsuario, codAerolinea, emailUsuario, telefonoUsuario)
VALUES
    ('Administrador de prueba', '2637a5c30af69a7bad877fdb65fbd78b', 'administrador', 'activo', NULL, 'admin@viajair.test', '3415550001'),
    ('CEO de prueba', '10b92a76e6a9ce8c1052ec2ce8a7fc3b', 'ceo', 'activo',
        (SELECT codAerolinea FROM AEROLINEAS WHERE codigoIATA = 'ARG'),
        'ceo@viajair.test', '3415550002'),
    ('Pasajero de prueba', '27eb0b7c4a4b620159fef5a83e52b69b', 'usuario', 'activo', NULL, 'usuario@viajair.test', '3415550003'),
    ('CEO pendiente de prueba', 'fbbbabf6510c8429d49924ff665a2e39', 'ceo', 'pendiente', NULL, 'ceo.pendiente@viajair.test', '3415550004');

INSERT INTO VUELOS
    (codAerolinea, origenVuelo, destinoVuelo, fechaSalidaVuelo, horaSalidaVuelo, precioVuelo, asientosDisponibles)
VALUES
    ((SELECT codAerolinea FROM AEROLINEAS WHERE codigoIATA = 'ARG'), 'Rosario', 'Buenos Aires', '2026-11-10', '08:30:00', 45000.00, 120),
    ((SELECT codAerolinea FROM AEROLINEAS WHERE codigoIATA = 'ARG'), 'Buenos Aires', 'Bariloche', '2026-11-15', '10:00:00', 95000.00, 80),
    ((SELECT codAerolinea FROM AEROLINEAS WHERE codigoIATA = 'LAN'), 'Santiago', 'Lima', '2026-11-20', '14:15:00', 180000.00, 150),
    ((SELECT codAerolinea FROM AEROLINEAS WHERE codigoIATA = 'IBE'), 'Madrid', 'Barcelona', '2026-11-25', '07:45:00', 75000.00, 100);

INSERT INTO PROMOCIONES
    (descripcionPromocion, descuentoPromocion, codAerolinea, estadoPromocion)
VALUES
    ('Descuento especial para vuelos nacionales.', 15.00,
        (SELECT codAerolinea FROM AEROLINEAS WHERE codigoIATA = 'ARG'), 'aprobada'),
    ('Promoción de lanzamiento para vuelos regionales.', 10.00,
        (SELECT codAerolinea FROM AEROLINEAS WHERE codigoIATA = 'LAN'), 'aprobada'),
    ('Nueva promoción pendiente de revisión.', 20.00,
        (SELECT codAerolinea FROM AEROLINEAS WHERE codigoIATA = 'IBE'), 'pendiente');

INSERT INTO NOVEDADES
    (tituloNovedad, textoNovedad, fechaPublicacionNovedad, fechaExpiracionNovedad)
VALUES
    ('Nuevos destinos disponibles', 'Ya podés consultar vuelos a nuevos destinos desde nuestro buscador.', '2026-10-01', '2026-12-31'),
    ('Promociones de temporada', 'Aprovechá los descuentos vigentes en vuelos seleccionados.', '2026-10-15', '2026-12-15');

INSERT INTO RESERVAS
    (codUsuario, codVuelo, cantidadPasajes, fechaReserva, precioTotal, estadoReserva)
VALUES
    ((SELECT codUsuario FROM USUARIOS WHERE emailUsuario = 'usuario@viajair.test'),
        (SELECT codVuelo FROM VUELOS WHERE origenVuelo = 'Rosario' AND destinoVuelo = 'Buenos Aires' AND fechaSalidaVuelo = '2026-11-10'),
        2, '2026-10-20', 90000.00, 'confirmada'),
    ((SELECT codUsuario FROM USUARIOS WHERE emailUsuario = 'usuario@viajair.test'),
        (SELECT codVuelo FROM VUELOS WHERE origenVuelo = 'Buenos Aires' AND destinoVuelo = 'Bariloche' AND fechaSalidaVuelo = '2026-11-15'),
        1, '2026-10-21', 95000.00, 'pendiente de pago'),
    ((SELECT codUsuario FROM USUARIOS WHERE emailUsuario = 'usuario@viajair.test'),
        (SELECT codVuelo FROM VUELOS WHERE origenVuelo = 'Santiago' AND destinoVuelo = 'Lima' AND fechaSalidaVuelo = '2026-11-20'),
        1, '2026-10-22', 180000.00, 'cancelada');
