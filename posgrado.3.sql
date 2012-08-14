-- phpMyAdmin SQL Dump
-- version 3.4.5deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 04, 2012 at 04:12 PM
-- Server version: 5.1.63
-- PHP Version: 5.3.6-13ubuntu3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `posgrado`
--

-- --------------------------------------------------------

--
-- Table structure for table `Academico`
--

CREATE TABLE IF NOT EXISTS `Academico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `institucion` int(11) DEFAULT NULL,
  `carrera` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `fecha_ingreso` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_egreso` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `forma_titulacion` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `lugar_estudio` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Areas_Interes`
--

CREATE TABLE IF NOT EXISTS `Areas_Interes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Areas_Usuarios`
--

CREATE TABLE IF NOT EXISTS `Areas_Usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `area_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Contacto`
--

CREATE TABLE IF NOT EXISTS `Contacto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `email` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `tel_movil` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `tel_fijo` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `contactar` int(11) DEFAULT NULL,
  `forma_enterado` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Docentes`
--

CREATE TABLE IF NOT EXISTS `Docentes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `grado` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `sni` int(11) DEFAULT NULL,
  `nivel_sni` int(11) DEFAULT NULL,
  `tiempo_completo` int(11) DEFAULT NULL,
  `especialidad` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `cedula` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `promep` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Estados`
--

CREATE TABLE IF NOT EXISTS `Estados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_comun` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `nombre_oficial` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `clave` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `abreviatura` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Estatus_PROMEP`
--

CREATE TABLE IF NOT EXISTS `Estatus_PROMEP` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Eventos`
--

CREATE TABLE IF NOT EXISTS `Eventos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `autor` int(11) DEFAULT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `fecha_inicio` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_fin` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `prioridad` int(11) DEFAULT NULL,
  `fecha_creado` varchar(10) COLLATE utf8_bin NOT NULL,
  `hora_inicio` varchar(10) CHARACTER SET utf8 NOT NULL,
  `hora_fin` varchar(10) CHARACTER SET utf8 NOT NULL,
  `validado` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Evento_Usuario`
--

CREATE TABLE IF NOT EXISTS `Evento_Usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Forma_enterado`
--

CREATE TABLE IF NOT EXISTS `Forma_enterado` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Herramientas`
--

CREATE TABLE IF NOT EXISTS `Herramientas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Herramientas_Usuarios`
--

CREATE TABLE IF NOT EXISTS `Herramientas_Usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `herramienta_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Idiomas`
--

CREATE TABLE IF NOT EXISTS `Idiomas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Idiomas_Usuarios`
--

CREATE TABLE IF NOT EXISTS `Idiomas_Usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `idioma_id` int(11) DEFAULT NULL,
  `lee` int(11) DEFAULT NULL,
  `escribe` int(11) DEFAULT NULL,
  `habla` int(11) DEFAULT NULL,
  `entiende` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Instituciones`
--

CREATE TABLE IF NOT EXISTS `Instituciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `abreviatura` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Laboral`
--

CREATE TABLE IF NOT EXISTS `Laboral` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `ha_trabajado` int(11) DEFAULT NULL,
  `experiencia` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `tiempo_trabajado` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Lenguajes`
--

CREATE TABLE IF NOT EXISTS `Lenguajes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Lenguajes_Usuarios`
--

CREATE TABLE IF NOT EXISTS `Lenguajes_Usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `lenguaje_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Lineas_investigacion`
--

CREATE TABLE IF NOT EXISTS `Lineas_investigacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Localidades`
--

CREATE TABLE IF NOT EXISTS `Localidades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `municipio_id` int(11) DEFAULT NULL,
  `clave` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `nombre` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `latitud` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `longitud` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `altitud` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Materias`
--

CREATE TABLE IF NOT EXISTS `Materias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `semestre` int(11) DEFAULT NULL,
  `linea_investigacion` int(11) DEFAULT NULL,
  `doc` int(11) DEFAULT NULL,
  `tis` int(11) DEFAULT NULL,
  `tps` int(11) DEFAULT NULL,
  `horas_totales` int(11) DEFAULT NULL,
  `creditos` int(11) DEFAULT NULL,
  `link_pdf` text COLLATE utf8_bin,
  `tipo` int(2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Municipios`
--

CREATE TABLE IF NOT EXISTS `Municipios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estado_id` int(11) DEFAULT NULL,
  `clave` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `nombre` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Nivel_SNI`
--

CREATE TABLE IF NOT EXISTS `Nivel_SNI` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Noticias`
--

CREATE TABLE IF NOT EXISTS `Noticias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` text CHARACTER SET utf8,
  `slug` text CHARACTER SET utf8,
  `contenido` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `link_imagen` text COLLATE utf8_bin NOT NULL,
  `creado` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Oauth`
--

CREATE TABLE IF NOT EXISTS `Oauth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `provider` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `uid` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Personal`
--

CREATE TABLE IF NOT EXISTS `Personal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `nombre` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `ap_paterno` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `ap_materno` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `fdn` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Fecha de Nacimiento',
  `sexo` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `procedencia` int(11) DEFAULT NULL,
  `calle` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `num_ext` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `num_int` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `colonia` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `cp` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Plataformas`
--

CREATE TABLE IF NOT EXISTS `Plataformas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Plataformas_Usuarios`
--

CREATE TABLE IF NOT EXISTS `Plataformas_Usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `plataforma_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Posgrado`
--

CREATE TABLE IF NOT EXISTS `Posgrado` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `nombre_tesis` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `asesor` int(11) DEFAULT NULL,
  `fecha_asignacion` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_fin_curso` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_titulacion` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `generacion` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `linea_investigacion` int(11) DEFAULT NULL,
  `link_tesis` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `link_exposicion` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Publicaciones`
--

CREATE TABLE IF NOT EXISTS `Publicaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `autor` int(11) DEFAULT NULL,
  `coautores` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `tipo` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `evento` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `fecha_publicacion` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `editorial` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `isbn` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `tipo_trabajo` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `nacionalidad` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Roles`
--

CREATE TABLE IF NOT EXISTS `Roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=4 ;

--
-- Dumping data for table `Roles`
--

INSERT INTO `Roles` (`id`, `nombre`) VALUES
(1, 'Docente'),
(2, 'Aspirante'),
(3, 'Alumno');

-- --------------------------------------------------------

--
-- Table structure for table `Secciones`
--

CREATE TABLE IF NOT EXISTS `Secciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `slug` text CHARACTER SET utf8,
  `contenido` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `actualizado` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Usuarios`
--

CREATE TABLE IF NOT EXISTS `Usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `password` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `activo` int(1) NOT NULL DEFAULT '1',
  `creado` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `actualizado` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Usuario_Roles`
--

CREATE TABLE IF NOT EXISTS `Usuario_Roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `rol_id` int(11) DEFAULT NULL,
  `creado` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2 ;

--
-- Dumping data for table `Usuario_Roles`
--

INSERT INTO `Usuario_Roles` (`id`, `usuario_id`, `rol_id`, `creado`) VALUES
(1, 1, 1, NULL);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
