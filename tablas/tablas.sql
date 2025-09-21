
USE registro_ninos;

-- Tabla principal de niños
CREATE TABLE IF NOT EXISTS ninos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ci_nino VARCHAR(10) NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    sexo ENUM('Masculino', 'Femenino') NOT NULL,
    provincia VARCHAR(50) NOT NULL,
    canton VARCHAR(50) NOT NULL,
    parroquia VARCHAR(50),
    barrio VARCHAR(50),
    direccion VARCHAR(150),
    estudiante_activo ENUM('Si','No') NOT NULL,
    grado VARCHAR(50),
    discapacitado ENUM('Si','No') NOT NULL,
    detalle_discapacidad TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de representantes (soporta 1 o 2 por niño)
CREATE TABLE IF NOT EXISTS representantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_nino INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    ci VARCHAR(10), -- Ahora válido también para el Representante 2
    parentesco VARCHAR(50),
    telefono VARCHAR(15),
    representante_numero ENUM('1','2') NOT NULL,
    FOREIGN KEY (id_nino) REFERENCES ninos(id) ON DELETE CASCADE
);
