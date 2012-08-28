<?php
class Publicacion extends ActiveRecord\Model {
	static $table_name = 'Publicaciones';
	static $has_one = array(		
	);
	static $has_many = array(
	);
	static $belongs_to = array(
		array('usuario', 'class_name' => 'Usuario')
	);
	
	static $alias_attribute = array(
		"fecha" => "fecha_publicacion",
	);
}
?>