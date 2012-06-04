-- ---
-- Globals
-- ---

-- SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
-- SET FOREIGN_KEY_CHECKS=0;

-- ---
-- Table 'Usuarios'
-- 
-- ---

DROP TABLE IF EXISTS `Usuarios`;
		
CREATE TABLE `Usuarios` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `personal_id` INTEGER NULL DEFAULT NULL,
  `academico_id` INTEGER NULL DEFAULT NULL,
  `contacto_id` INTEGER NULL DEFAULT NULL,
  `laboral_id` INTEGER NULL DEFAULT NULL,
  `posgrado_id` INTEGER NULL DEFAULT NULL,
  `docente_id` INTEGER NULL DEFAULT NULL,
  `login` MEDIUMTEXT NULL DEFAULT NULL,
  `password` MEDIUMTEXT NULL DEFAULT NULL,
  `creado` VARCHAR(10) NULL DEFAULT NULL,
  `actualizado` VARCHAR(10) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Personal'
-- 
-- ---

DROP TABLE IF EXISTS `Personal`;
		
CREATE TABLE `Personal` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `nombre` MEDIUMTEXT NULL DEFAULT NULL,
  `ap_paterno` MEDIUMTEXT NULL DEFAULT NULL,
  `ap_materno` MEDIUMTEXT NULL DEFAULT NULL,
  `fdn` VARCHAR(10) NULL DEFAULT NULL COMMENT 'Fecha de Nacimiento',
  `sexo` VARCHAR(1) NULL DEFAULT NULL,
  `procedencia` INTEGER NULL DEFAULT NULL,
  `calle` MEDIUMTEXT NULL DEFAULT NULL,
  `num_ext` MEDIUMTEXT NULL DEFAULT NULL,
  `num_int` MEDIUMTEXT NULL DEFAULT NULL,
  `colonia` MEDIUMTEXT NULL DEFAULT NULL,
  `cp` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Academico'
-- 
-- ---

DROP TABLE IF EXISTS `Academico`;
		
CREATE TABLE `Academico` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `institucion` INTEGER NULL DEFAULT NULL,
  `carrera` MEDIUMTEXT NULL DEFAULT NULL,
  `fecha_ingreso` VARCHAR(10) NULL DEFAULT NULL,
  `fecha_egreso` VARCHAR(10) NULL DEFAULT NULL,
  `forma_titulacion` MEDIUMTEXT NULL DEFAULT NULL,
  `lugar_estudio` INTEGER NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Contacto'
-- 
-- ---

DROP TABLE IF EXISTS `Contacto`;
		
CREATE TABLE `Contacto` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `email` MEDIUMTEXT NULL DEFAULT NULL,
  `tel_movil` MEDIUMTEXT NULL DEFAULT NULL,
  `tel_fijo` MEDIUMTEXT NULL DEFAULT NULL,
  `contactar` INTEGER NULL DEFAULT NULL,
  `forma_enterado` INTEGER NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Laboral'
-- 
-- ---

DROP TABLE IF EXISTS `Laboral`;
		
CREATE TABLE `Laboral` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `ha_trabajado` INTEGER NULL DEFAULT NULL,
  `experiencia` TEXT NULL DEFAULT NULL,
  `tiempo_trabajado` INTEGER NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Posgrado'
-- 
-- ---

DROP TABLE IF EXISTS `Posgrado`;
		
CREATE TABLE `Posgrado` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `nombre_tesis` MEDIUMTEXT NULL DEFAULT NULL,
  `asesor` INTEGER NULL DEFAULT NULL,
  `fecha_asignacion` VARCHAR(10) NULL DEFAULT NULL,
  `fecha_fin_curso` VARCHAR(10) NULL DEFAULT NULL,
  `fecha_titulacion` VARCHAR(10) NULL DEFAULT NULL,
  `generacion` MEDIUMTEXT NULL DEFAULT NULL,
  `linea_investigacion` INTEGER NULL DEFAULT NULL,
  `link_tesis` MEDIUMTEXT NULL DEFAULT NULL,
  `link_exposicion` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Localidades'
-- 
-- ---

DROP TABLE IF EXISTS `Localidades`;
		
CREATE TABLE `Localidades` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `municipio_id` INTEGER NULL DEFAULT NULL,
  `clave` MEDIUMTEXT NULL DEFAULT NULL,
  `nombre` MEDIUMTEXT NULL DEFAULT NULL,
  `latitud` MEDIUMTEXT NULL DEFAULT NULL,
  `longitud` MEDIUMTEXT NULL DEFAULT NULL,
  `altitud` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Municipios'
-- 
-- ---

DROP TABLE IF EXISTS `Municipios`;
		
CREATE TABLE `Municipios` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `estado_id` INTEGER NULL DEFAULT NULL,
  `clave` MEDIUMTEXT NULL DEFAULT NULL,
  `nombre` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Estados'
-- 
-- ---

DROP TABLE IF EXISTS `Estados`;
		
CREATE TABLE `Estados` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `nombre_comun` MEDIUMTEXT NULL DEFAULT NULL,
  `nombre_oficial` MEDIUMTEXT NULL DEFAULT NULL,
  `clave` MEDIUMTEXT NULL DEFAULT NULL,
  `abreviatura` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Lineas_investigacion'
-- 
-- ---

DROP TABLE IF EXISTS `Lineas_investigacion`;
		
CREATE TABLE `Lineas_investigacion` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `nombre` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Instituciones'
-- 
-- ---

DROP TABLE IF EXISTS `Instituciones`;
		
CREATE TABLE `Instituciones` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `nombre` MEDIUMTEXT NULL DEFAULT NULL,
  `abreviatura` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Docentes'
-- 
-- ---

DROP TABLE IF EXISTS `Docentes`;
		
CREATE TABLE `Docentes` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `grado` MEDIUMTEXT NULL DEFAULT NULL,
  `sni` INTEGER NULL DEFAULT NULL,
  `nivel_sni` INTEGER NULL DEFAULT NULL,
  `tiempo_completo` INTEGER NULL DEFAULT NULL,
  `especialidad` MEDIUMTEXT NULL DEFAULT NULL,
  `cedula` MEDIUMTEXT NULL DEFAULT NULL,
  `promep` INTEGER NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Nivel_SNI'
-- 
-- ---

DROP TABLE IF EXISTS `Nivel_SNI`;
		
CREATE TABLE `Nivel_SNI` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `nombre` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Estatus_PROMEP'
-- 
-- ---

DROP TABLE IF EXISTS `Estatus_PROMEP`;
		
CREATE TABLE `Estatus_PROMEP` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `nombre` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Forma_enterado'
-- 
-- ---

DROP TABLE IF EXISTS `Forma_enterado`;
		
CREATE TABLE `Forma_enterado` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `nombre` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Oauth'
-- 
-- ---

DROP TABLE IF EXISTS `Oauth`;
		
CREATE TABLE `Oauth` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `usuario_id` INTEGER NULL DEFAULT NULL,
  `provider` MEDIUMTEXT NULL DEFAULT NULL,
  `uid` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Idiomas'
-- 
-- ---

DROP TABLE IF EXISTS `Idiomas`;
		
CREATE TABLE `Idiomas` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `nombre` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Idiomas_Usuarios'
-- 
-- ---

DROP TABLE IF EXISTS `Idiomas_Usuarios`;
		
CREATE TABLE `Idiomas_Usuarios` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `usuario_id` INTEGER NULL DEFAULT NULL,
  `idioma_id` INTEGER NULL DEFAULT NULL,
  `lee` INTEGER NULL DEFAULT NULL,
  `escribe` INTEGER NULL DEFAULT NULL,
  `habla` INTEGER NULL DEFAULT NULL,
  `entiende` INTEGER NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Lenguajes'
-- 
-- ---

DROP TABLE IF EXISTS `Lenguajes`;
		
CREATE TABLE `Lenguajes` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `nombre` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Plataformas'
-- 
-- ---

DROP TABLE IF EXISTS `Plataformas`;
		
CREATE TABLE `Plataformas` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `nombre` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Herramientas'
-- 
-- ---

DROP TABLE IF EXISTS `Herramientas`;
		
CREATE TABLE `Herramientas` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `nombre` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Areas_Interes'
-- 
-- ---

DROP TABLE IF EXISTS `Areas_Interes`;
		
CREATE TABLE `Areas_Interes` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `nombre` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Plataformas_Usuarios'
-- 
-- ---

DROP TABLE IF EXISTS `Plataformas_Usuarios`;
		
CREATE TABLE `Plataformas_Usuarios` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `usuario_id` INTEGER NULL DEFAULT NULL,
  `plataforma_id` INTEGER NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Herramientas_Usuarios'
-- 
-- ---

DROP TABLE IF EXISTS `Herramientas_Usuarios`;
		
CREATE TABLE `Herramientas_Usuarios` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `usuario_id` INTEGER NULL DEFAULT NULL,
  `herramienta_id` INTEGER NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Lenguajes_Usuarios'
-- 
-- ---

DROP TABLE IF EXISTS `Lenguajes_Usuarios`;
		
CREATE TABLE `Lenguajes_Usuarios` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `usuario_id` INTEGER NULL DEFAULT NULL,
  `lenguaje_id` INTEGER NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Areas_Usuarios'
-- 
-- ---

DROP TABLE IF EXISTS `Areas_Usuarios`;
		
CREATE TABLE `Areas_Usuarios` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `usuario_id` INTEGER NULL DEFAULT NULL,
  `area_id` INTEGER NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Publicaciones'
-- 
-- ---

DROP TABLE IF EXISTS `Publicaciones`;
		
CREATE TABLE `Publicaciones` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `nombre` MEDIUMTEXT NULL DEFAULT NULL,
  `autor` INTEGER NULL DEFAULT NULL,
  `coautores` MEDIUMTEXT NULL DEFAULT NULL,
  `tipo` MEDIUMTEXT NULL DEFAULT NULL,
  `evento` MEDIUMTEXT NULL DEFAULT NULL,
  `fecha_publicacion` VARCHAR(10) NULL DEFAULT NULL,
  `editorial` MEDIUMTEXT NULL DEFAULT NULL,
  `isbn` MEDIUMTEXT NULL DEFAULT NULL,
  `tipo_trabajo` MEDIUMTEXT NULL DEFAULT NULL,
  `nacionalidad` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Secciones'
-- 
-- ---

DROP TABLE IF EXISTS `Secciones`;
		
CREATE TABLE `Secciones` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `nombre` MEDIUMTEXT NULL DEFAULT NULL,
  `contenido` TEXT NULL DEFAULT NULL,
  `actualizado` VARCHAR(10) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Roles'
-- 
-- ---

DROP TABLE IF EXISTS `Roles`;
		
CREATE TABLE `Roles` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `nombre` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Usuario_Roles'
-- 
-- ---

DROP TABLE IF EXISTS `Usuario_Roles`;
		
CREATE TABLE `Usuario_Roles` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `usuario_id` INTEGER NULL DEFAULT NULL,
  `rol_id` INTEGER NULL DEFAULT NULL,
  `creado` VARCHAR(10) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Eventos'
-- 
-- ---

DROP TABLE IF EXISTS `Eventos`;
		
CREATE TABLE `Eventos` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `nombre` MEDIUMTEXT NULL DEFAULT NULL,
  `autor` INTEGER NULL DEFAULT NULL,
  `descripcion` TEXT NULL DEFAULT NULL,
  `fecha_inicio` VARCHAR(10) NULL DEFAULT NULL,
  `fecha_fin` VARCHAR(10) NULL DEFAULT NULL,
  `prioridad` INTEGER NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Evento_Usuario'
-- 
-- ---

DROP TABLE IF EXISTS `Evento_Usuario`;
		
CREATE TABLE `Evento_Usuario` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `evento_id` INTEGER NULL DEFAULT NULL,
  `usuario_id` INTEGER NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'Materias'
-- 
-- ---

DROP TABLE IF EXISTS `Materias`;
		
CREATE TABLE `Materias` (
  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
  `nombre` MEDIUMTEXT NULL DEFAULT NULL,
  `semestre` INTEGER NULL DEFAULT NULL,
  `linea_investigacion` INTEGER NULL DEFAULT NULL,
  `doc` INTEGER NULL DEFAULT NULL,
  `tis` INTEGER NULL DEFAULT NULL,
  `tps` INTEGER NULL DEFAULT NULL,
  `horas_totales` INTEGER NULL DEFAULT NULL,
  `creditos` INTEGER NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- ---
-- Foreign Keys 
-- ---


-- ---
-- Table Properties
-- ---

 ALTER TABLE `Usuarios` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Personal` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Academico` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Contacto` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Laboral` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Posgrado` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Localidades` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Municipios` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Estados` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Lineas_investigacion` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Instituciones` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Docentes` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Nivel_SNI` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Estatus_PROMEP` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Forma_enterado` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Oauth` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Idiomas` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Idiomas_Usuarios` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Lenguajes` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Plataformas` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Herramientas` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Areas_Interes` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Plataformas_Usuarios` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Herramientas_Usuarios` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Lenguajes_Usuarios` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Areas_Usuarios` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Publicaciones` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Secciones` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Roles` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Usuario_Roles` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Eventos` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Evento_Usuario` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 ALTER TABLE `Materias` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ---
-- Test Data
-- ---

-- INSERT INTO `Usuarios` (`id`,`personal_id`,`academico_id`,`contacto_id`,`laboral_id`,`posgrado_id`,`docente_id`,`login`,`password`,`creado`,`actualizado`) VALUES
-- ('','','','','','','','','','','');
-- INSERT INTO `Personal` (`id`,`nombre`,`ap_paterno`,`ap_materno`,`fdn`,`sexo`,`procedencia`,`calle`,`num_ext`,`num_int`,`colonia`,`cp`) VALUES
-- ('','','','','','','','','','','','');
-- INSERT INTO `Academico` (`id`,`institucion`,`carrera`,`fecha_ingreso`,`fecha_egreso`,`forma_titulacion`,`lugar_estudio`) VALUES
-- ('','','','','','','');
-- INSERT INTO `Contacto` (`id`,`email`,`tel_movil`,`tel_fijo`,`contactar`,`forma_enterado`) VALUES
-- ('','','','','','');
-- INSERT INTO `Laboral` (`id`,`ha_trabajado`,`experiencia`,`tiempo_trabajado`) VALUES
-- ('','','','');
-- INSERT INTO `Posgrado` (`id`,`nombre_tesis`,`asesor`,`fecha_asignacion`,`fecha_fin_curso`,`fecha_titulacion`,`generacion`,`linea_investigacion`,`link_tesis`,`link_exposicion`) VALUES
-- ('','','','','','','','','','');
-- INSERT INTO `Localidades` (`id`,`municipio_id`,`clave`,`nombre`,`latitud`,`longitud`,`altitud`) VALUES
-- ('','','','','','','');
-- INSERT INTO `Municipios` (`id`,`estado_id`,`clave`,`nombre`) VALUES
-- ('','','','');
-- INSERT INTO `Estados` (`id`,`nombre_comun`,`nombre_oficial`,`clave`,`abreviatura`) VALUES
-- ('','','','','');
-- INSERT INTO `Lineas_investigacion` (`id`,`nombre`) VALUES
-- ('','');
-- INSERT INTO `Instituciones` (`id`,`nombre`,`abreviatura`) VALUES
-- ('','','');
-- INSERT INTO `Docentes` (`id`,`grado`,`sni`,`nivel_sni`,`tiempo_completo`,`especialidad`,`cedula`,`promep`) VALUES
-- ('','','','','','','','');
-- INSERT INTO `Nivel_SNI` (`id`,`nombre`) VALUES
-- ('','');
-- INSERT INTO `Estatus_PROMEP` (`id`,`nombre`) VALUES
-- ('','');
-- INSERT INTO `Forma_enterado` (`id`,`nombre`) VALUES
-- ('','');
-- INSERT INTO `Oauth` (`id`,`usuario_id`,`provider`,`uid`) VALUES
-- ('','','','');
-- INSERT INTO `Idiomas` (`id`,`nombre`) VALUES
-- ('','');
-- INSERT INTO `Idiomas_Usuarios` (`id`,`usuario_id`,`idioma_id`,`lee`,`escribe`,`habla`,`entiende`) VALUES
-- ('','','','','','','');
-- INSERT INTO `Lenguajes` (`id`,`nombre`) VALUES
-- ('','');
-- INSERT INTO `Plataformas` (`id`,`nombre`) VALUES
-- ('','');
-- INSERT INTO `Herramientas` (`id`,`nombre`) VALUES
-- ('','');
-- INSERT INTO `Areas_Interes` (`id`,`nombre`) VALUES
-- ('','');
-- INSERT INTO `Plataformas_Usuarios` (`id`,`usuario_id`,`plataforma_id`) VALUES
-- ('','','');
-- INSERT INTO `Herramientas_Usuarios` (`id`,`usuario_id`,`herramienta_id`) VALUES
-- ('','','');
-- INSERT INTO `Lenguajes_Usuarios` (`id`,`usuario_id`,`lenguaje_id`) VALUES
-- ('','','');
-- INSERT INTO `Areas_Usuarios` (`id`,`usuario_id`,`area_id`) VALUES
-- ('','','');
-- INSERT INTO `Publicaciones` (`id`,`nombre`,`autor`,`coautores`,`tipo`,`evento`,`fecha_publicacion`,`editorial`,`isbn`,`tipo_trabajo`,`nacionalidad`) VALUES
-- ('','','','','','','','','','','');
-- INSERT INTO `Secciones` (`id`,`nombre`,`contenido`,`actualizado`) VALUES
-- ('','','','');
-- INSERT INTO `Roles` (`id`,`nombre`) VALUES
-- ('','');
-- INSERT INTO `Usuario_Roles` (`id`,`usuario_id`,`rol_id`,`creado`) VALUES
-- ('','','','');
-- INSERT INTO `Eventos` (`id`,`nombre`,`autor`,`descripcion`,`fecha_inicio`,`fecha_fin`,`prioridad`) VALUES
-- ('','','','','','','');
-- INSERT INTO `Evento_Usuario` (`id`,`evento_id`,`usuario_id`) VALUES
-- ('','','');
-- INSERT INTO `Materias` (`id`,`nombre`,`semestre`,`linea_investigacion`,`doc`,`tis`,`tps`,`horas_totales`,`creditos`) VALUES
-- ('','','','','','','','','');
