<?php
class UsuariosRoles extends ActiveRecord\Model {
	static $table_name = 'Usuario_Roles';
	static $has_one = array(
	);
	static $has_many = array(
	);
	static $belongs_to = array(
		array('usuario','class_name' => 'Usuario'),
		array('rol','class_name' => 'Rol')
	);
	
	static $alias_attribute = array(
	);
}
?>