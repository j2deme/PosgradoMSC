<?php
class Institucion extends ActiveRecord\Model {
	static $table_name = 'Instituciones';
	static $has_one = array(		
	);
	static $has_many = array(
	);
	static $belongs_to = array(
	);
	static $alias_attribute = array(
		'abr' => 'abreviatura'
	);
}
?>