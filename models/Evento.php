<?php
class Evento extends ActiveRecord\Model {
	static $table_name = 'Eventos';
	static $has_one = array(
		array('autor', 'class_name' => 'Usuario')		
	);
	static $has_many = array(
		array('ue', 'class_name' => 'UsuariosEventos'),
		array('invitados', 'class_name' => 'Usuario', 'through' => 'ue')
	);
	static $belongs_to = array(
	);
	
	static $alias_attribute = array(
		'inicio' => 'fecha_inicio',
		'fin' => 'fecha_fin',
		'creado' => 'fecha_creado'
	);
}
?>