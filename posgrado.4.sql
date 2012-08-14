-- phpMyAdmin SQL Dump
-- version 3.4.5deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 07, 2012 at 11:20 PM
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=4 ;

--
-- Dumping data for table `Academico`
--

INSERT INTO `Academico` (`id`, `usuario_id`, `institucion`, `carrera`, `fecha_ingreso`, `fecha_egreso`, `forma_titulacion`, `lugar_estudio`) VALUES
(1, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 2, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 3, NULL, NULL, NULL, NULL, NULL, NULL);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=4 ;

--
-- Dumping data for table `Contacto`
--

INSERT INTO `Contacto` (`id`, `usuario_id`, `email`, `tel_movil`, `tel_fijo`, `contactar`, `forma_enterado`) VALUES
(1, 1, 'j2deme@gmail.com', '', '', 0, NULL),
(2, 2, 'luis@gmail.com', '', '', 0, NULL),
(3, 3, 'j2deme@gmail.com', '', '', 0, NULL);

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
  `cv` text COLLATE utf8_bin NOT NULL,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=4 ;

--
-- Dumping data for table `Laboral`
--

INSERT INTO `Laboral` (`id`, `usuario_id`, `ha_trabajado`, `experiencia`, `tiempo_trabajado`) VALUES
(1, NULL, NULL, NULL, NULL),
(2, NULL, NULL, NULL, NULL),
(3, NULL, NULL, NULL, NULL);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=4 ;

--
-- Dumping data for table `Personal`
--

INSERT INTO `Personal` (`id`, `usuario_id`, `nombre`, `ap_paterno`, `ap_materno`, `fdn`, `sexo`, `procedencia`, `calle`, `num_ext`, `num_int`, `colonia`, `cp`) VALUES
(1, 1, 'Jaime Jesús', 'Delgado', 'Meraz', NULL, '1', NULL, 'Maguey esq. Textil', '2171', NULL, 'Fracc. Cuauhtemoc', '87060'),
(2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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
  `usuario_id` int(11) DEFAULT NULL,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=6 ;

--
-- Dumping data for table `Roles`
--

INSERT INTO `Roles` (`id`, `nombre`) VALUES
(1, 'Docente'),
(2, 'Aspirante'),
(3, 'Alumno'),
(4, 'No aceptado'),
(5, 'Administrador');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=4 ;

--
-- Dumping data for table `Usuarios`
--

INSERT INTO `Usuarios` (`id`, `login`, `password`, `activo`, `creado`, `actualizado`) VALUES
(1, 'j2deme', '827ccb0eea8a706c4c34a16891f84e7b', 1, '1341437123', '1341532710'),
(2, 'Luis', 'afec0e4fa78bd8e45c2d200df3264211', 1, '1341438975', '1341532658'),
(3, 'admin', '21232f297a57a5a743894a0e4a801fc3', 0, '1341526969', '1341526999');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=5 ;

--
-- Dumping data for table `Usuario_Roles`
--

INSERT INTO `Usuario_Roles` (`id`, `usuario_id`, `rol_id`, `creado`) VALUES
(1, 1, 2, NULL),
(3, 2, 3, NULL),
(4, 3, 5, NULL);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
