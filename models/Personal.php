<?php
class Personal extends ActiveRecord\Model {
	static $table_name = 'Personal';
	static $has_one = array(
		array('procedencia','class_name' => 'Localidad')		
	);
	static $has_many = array(
	);
	static $belongs_to = array(
	);
	
	static $alias_attribute = array(
		'paterno' => 'ap_paterno',
		'materno' => 'ap_materno'
	);
}
?>
