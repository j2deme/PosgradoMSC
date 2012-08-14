<?php
class Contacto extends ActiveRecord\Model {
	static $table_name = 'Contacto';
	static $has_one = array(
		array('forma', 'class_name' => 'FormaEnterado'),		
	);
	static $has_many = array(
	);
	static $belongs_to = array(
	);
	
	static $alias_attribute = array(
		'movil' => 'tel_movil',
		'fijo' => 'tel_fijo',
		'forma' => 'forma_enterado'
	);
}
?>