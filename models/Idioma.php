<?php
class Idioma extends ActiveRecord\Model {
	static $table_name = 'Idiomas';
	static $has_one = array(		
	);
	static $has_many = array(
		array('ui', 'class_name' => 'UsuariosIdiomas'),
		array('usuarios', 'class_name' => 'Usuario', 'through' => 'ui')
	);
	static $belongs_to = array(
	);
	
	static $alias_attribute = array(
	);
}
?>