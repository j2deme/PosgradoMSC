<?php
class UsuariosPlataformas extends ActiveRecord\Model {
	static $table_name = 'Plataformas_Usuarios';
	static $has_one = array(
	);
	static $has_many = array(
	);
	static $belongs_to = array(
		array('usuario','class_name' => 'Usuario'),
		array('plataforma','class_name' => 'Plataforma')
	);
	
	static $alias_attribute = array(
	'conocimiento' => 'plataforma_id'
	);
}
?>