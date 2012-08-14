<?php
class UsuariosHerramientas extends ActiveRecord\Model {
	static $table_name = 'Herramientas_Usuarios';
	static $has_one = array(
	);
	static $has_many = array(
	);
	static $belongs_to = array(
		array('usuario','class_name' => 'Usuario'),
		array('herramienta','class_name' => 'Herramienta')
	);
	
	static $alias_attribute = array(
//		'herramienta' => 'herramienta_id',
//		'usuario' => 'usuario_id'
	);
}
?>