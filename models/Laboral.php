<?php
class Laboral extends ActiveRecord\Model {
	static $table_name = 'Laboral';
	static $has_one = array(		
	);
	static $has_many = array(
	);
	static $belongs_to = array(
	);
	
	static $alias_attribute = array(
		'trabajado' => 'ha_trabajado',
		'tiempo' => 'tiempo_trabajado'
	);
}
?>