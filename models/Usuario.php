<?php
class Usuario extends ActiveRecord\Model {
	static $table_name = 'Usuarios';
	static $has_one = array(
		array('personal', 'class_name' => 'Personal'),
		array('academico', 'class_name' => 'Academico'),
		array('contacto', 'class_name' => 'Contacto'),
		array('laboral', 'class_name' => 'Laboral'),
		array('pg', 'class_name' => 'Posgrado'),
		array('docente', 'class_name' => 'Docente')
	);
	static $has_many = array(
		array('oauth','class_name' => 'Oauth'),
		array('ua', 'class_name' => 'UsuariosAreas'),
		array('intereses', 'class_name' => 'AreaInteres', 'through' => 'ua'),
		array('ue', 'class_name' => 'UsuariosEventos'),
		array('eventos', 'class_name' => 'Evento', 'through' => 'ue'),
		array('uh', 'class_name' => 'UsuariosHerramientas'),
		array('herramientas', 'class_name' => 'Herramienta', 'through' => 'uh'),
		array('ui', 'class_name' => 'UsuariosIdiomas'),
		array('idiomas', 'class_name' => 'Idioma', 'through' => 'ui'),
		array('ul', 'class_name' => 'UsuariosLenguajes'),
		array('lenguajes', 'class_name' => 'Lenguaje', 'through' => 'ul'),
		array('up', 'class_name' => 'UsuariosPlataformas'),
		array('plataformas', 'class_name' => 'Plataforma', 'through' => 'up'),
		array('publicaciones', 'class_name' => 'Publicacion'),
		array('ur', 'class_name' => 'UsuariosRoles'),
		array('roles', 'class_name' => 'Rol', 'through' => 'ur')
	);
	static $belongs_to = array(
	);

	static $alias_attribute = array(
#		'personal' => 'personal_id',
#		'academico' => 'academico_id',
#		'contacto' => 'contacto_id',
#		'laboral' => 'laboral_id',
#		'pg' => 'posgrado_id',
#		'docente' => 'docente_id',
		'usuario' 	=> 'login',
	);
}
?>
