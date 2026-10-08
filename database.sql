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
ALTER TABLE USUARIOS ADD tokenRecuperacion VARCHAR(100) NULL AFTER estadoUsuario;
ALTER TABLE USUARIOS MODIFY claveUsuario VARCHAR(32) NOT NULL;