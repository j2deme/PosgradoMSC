<?php
class Lenguaje extends ActiveRecord\Model {
	static $table_name = 'Lenguajes';
	static $has_one = array(		
	);
	static $has_many = array(
		array('ul', 'class_name' => 'UsuariosLenguajes'),
		array('usuarios', 'class_name' => 'Usuario', 'through' => 'ul')
	);
	static $belongs_to = array(
	);
	
	static $alias_attribute = array(
	);
}
?>