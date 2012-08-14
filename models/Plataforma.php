<?php
class Plataforma extends ActiveRecord\Model {
	static $table_name = 'Plataformas';
	static $has_one = array(		
	);
	static $has_many = array(
		array('up', 'class_name' => 'UsuariosPlataformas'),
		array('usuarios', 'class_name' => 'Usuario', 'through' => 'up')
	);
	static $belongs_to = array(
	);
	
	static $alias_attribute = array(
	);
}
?>