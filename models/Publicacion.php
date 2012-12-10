<?php
class Publicacion extends ActiveRecord\Model {
	static $table_name = 'Publicaciones';
	static $has_one = array(		
	);
	static $has_many = array(
		array('upu', 'class_name' => 'UsuariosPublicaciones'),
		array('usuarios', 'class_name' => 'Usuario','through' => 'upu')
	);
	static $belongs_to = array(
		
	);
	
	static $alias_attribute = array(
		"fecha" => "fecha_publicacion",
	);
}
?>