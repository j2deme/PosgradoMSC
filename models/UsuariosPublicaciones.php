<?php
class UsuariosPublicaciones extends ActiveRecord\Model {
	static $table_name = 'Publicaciones_Usuarios';
	static $has_one = array(
	);
	static $has_many = array(
	);
	static $belongs_to = array(
		array('usuario','class_name' => 'Usuario'),
		array('publicacion','class_name' => 'Publicacion')
	);
	
	static $alias_attribute = array(

	);
}
?>