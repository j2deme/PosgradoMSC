<?php
class Academico extends ActiveRecord\Model {
	static $table_name = 'Academico';
	static $has_one = array(
		array('institucion','class_name' => 'Institucion'),
		array('ubicacion','class_name' => 'Localidad')	
	);
	static $has_many = array(
	);
	static $belongs_to = array(
	);
	
	static $alias_attribute = array(
		'ingreso' => 'fecha_ingreso',
		'egreso' => 'fecha_egreso',
		'titulacion' => 'forma_titulacion',
		'ubicacion' => 'lugar_estudio'
	);
}
?>