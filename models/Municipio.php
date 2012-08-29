<?php
class Municipio extends ActiveRecord\Model {
	static $table_name = 'Municipios';
	static $has_one = array(
	);
	static $has_many = array(
		array('localidades', 'class_name' => 'Localidad')
	);
	static $belongs_to = array(
		array('estado', 'class_name' => 'Estado')
	);
	
	static $alias_attribute = array(
		'estado' => 'estado_id'
	);
}
?>