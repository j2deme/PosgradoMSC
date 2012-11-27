<?php
class UsuariosLenguajes extends ActiveRecord\Model {
	static $table_name = 'Lenguajes_Usuarios';
	static $has_one = array(
	);
	static $has_many = array(
	);
	static $belongs_to = array(
		array('usuario','class_name' => 'Usuario'),
		array('lenguaje','class_name' => 'Lenguaje')
	);
	
	static $alias_attribute = array(
	'conocimiento' => 'lenguaje_id'
	);
}
?>