-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: pdb1050.awardspace.net
-- Tiempo de generación: 18-09-2025 a las 19:00:17
-- Versión del servidor: 8.0.32
-- Versión de PHP: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `4476127_sistema`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ninos`
--

CREATE TABLE `ninos` (
  `id` int NOT NULL,
  `ci_nino` varchar(10) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `sexo` enum('Masculino','Femenino') NOT NULL,
  `provincia` varchar(50) NOT NULL,
  `canton` varchar(50) NOT NULL,
  `parroquia` varchar(50) DEFAULT NULL,
  `barrio` varchar(50) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `estudiante_activo` enum('Si','No') NOT NULL,
  `grado` varchar(50) DEFAULT NULL,
  `discapacitado` enum('Si','No') NOT NULL,
  `detalle_discapacidad` text,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `ninos`
--

INSERT INTO `ninos` (`id`, `ci_nino`, `nombre_completo`, `fecha_nacimiento`, `sexo`, `provincia`, `canton`, `parroquia`, `barrio`, `direccion`, `estudiante_activo`, `grado`, `discapacitado`, `detalle_discapacidad`, `fecha_registro`) VALUES
(4, '1716537160', 'ECHEVERRIA MARX', '2006-02-02', 'Femenino', 'Esmeraldas', 'Esmeraldas', 'Atacames', 'Tonsupa', 'Av. Club del Pacifico', 'Si', '10', 'No', '', '2025-09-18 16:32:51'),
(6, '1716537164', 'CARDENAS ELENA', '2001-09-09', 'Masculino', 'Esmeraldas', 'Esmeraldas', 'Atacames', 'PADIFICO', 'AV 26\\r\\nClub del pacifico', 'Si', '', 'Si', '', '2025-09-18 16:52:18'),
(7, '1716537160', 'MACIAS ANDRES', '2005-01-02', 'Masculino', 'Esmeraldas', 'Esmeraldas', 'Tonchigüe', 'PADIFICO', 'Villarica', 'Si', '10', 'No', '', '2025-09-18 16:57:47');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ninos`
--
ALTER TABLE `ninos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ninos`
--
ALTER TABLE `ninos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
