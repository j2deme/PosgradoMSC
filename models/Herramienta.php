<?php
class Herramienta extends ActiveRecord\Model {
	static $table_name = 'Herramientas';
	static $has_one = array(		
	);
	static $has_many = array(
		array('uh', 'class_name' => 'UsuariosHerramientas'),
		array('usuarios', 'class_name' => 'Usuario', 'through' => 'uh')
	);
	static $belongs_to = array(
	);
	
	static $alias_attribute = array(
	);
}
?>