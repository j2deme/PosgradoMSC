<?php
class Estado extends ActiveRecord\Model {
	static $table_name = 'Estados';
	static $has_one = array(			
	);
	static $has_many = array(
		array('municipios', 'class_name' => 'Municipio'),
		array('localidades', 'class_name' => 'Localidad', 'through' => 'municipios')
	);
	static $belongs_to = array(
	);
	
	static $alias_attribute = array(
		'nombre' => 'nombre_comun',
		'oficial' => 'nombre_oficial',
		'abr' => 'abreviatura'
	);
}
?>