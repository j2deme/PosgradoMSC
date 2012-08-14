<?php
class UsuariosEventos extends ActiveRecord\Model {
	static $table_name = 'Evento_Usuario';
	static $has_one = array(
	);
	static $has_many = array(
	);
	static $belongs_to = array(
		array('evento','class_name' => 'Evento'),
		array('usuario','class_name' => 'Usuario')
	);
	
	static $alias_attribute = array(
//		'evento' => 'evento_id',
//		'usuario' => 'usuario_id'
	);
}
?>