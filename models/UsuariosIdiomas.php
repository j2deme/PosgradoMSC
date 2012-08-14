<?php
class UsuariosIdiomas extends ActiveRecord\Model {
	static $table_name = 'Idiomas_Usuarios';
	static $has_one = array(
	);
	static $has_many = array(
	);
	static $belongs_to = array(
		array('usuario','class_name' => 'Usuario'),
		array('idioma','class_name' => 'Idioma')
	);
	
	static $alias_attribute = array(
//		'herramienta' => 'herramienta_id',
//		'usuario' => 'usuario_id'
	);
}
?>