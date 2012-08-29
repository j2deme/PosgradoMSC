<?php
class Localidad extends ActiveRecord\Model {
	static $table_name = 'Localidades';
	static $has_one = array(
				
	);
	static $has_many = array(
	);
	static $belongs_to = array(
		array('municipio', 'class_name' => 'Municipio'),
	);
	
	static $alias_attribute = array(
		'municipio' => 'municipio_id'
	);
}
?>