<?php
class AreaInteres extends ActiveRecord\Model {
	static $table_name = 'Areas_Interes';
	static $has_one = array(		
	);
	static $has_many = array(
		array('ua', 'class_name' => 'UsuariosAreas'),
		array('usuarios', 'class_name' => 'Usuario', 'through' => 'ua')
	);
	static $belongs_to = array(
	);
	
	static $alias_attribute = array(
	);
}
?>