<?php
class Rol extends ActiveRecord\Model {
	static $table_name = 'Roles';
	static $has_one = array(		
	);
	static $has_many = array(
		array('ur', 'class_name' => 'UsuariosRoles'),
		array('usuarios', 'class_name' => 'Usuario', 'through' => 'ur')
	);
	static $belongs_to = array(
	);
	
	static $alias_attribute = array(
	);
}
?>