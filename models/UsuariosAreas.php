<?php
class UsuariosAreas extends ActiveRecord\Model {
	static $table_name = 'Areas_Usuarios';
	static $has_one = array(		
	);
	static $has_many = array(
	);
	static $belongs_to = array(
		array('usuario', 'class_name' => 'Usuario'),
		array('interes', 'class_name' => 'AreaInteres')
	);
	
	static $alias_attribute = array(
	);
}
?>