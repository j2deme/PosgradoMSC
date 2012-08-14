<?php
class LineaInvestigacion extends ActiveRecord\Model {
	static $table_name = 'Lineas_investigacion';
	static $has_one = array(		
	);
	static $has_many = array(
		array('materias','class_name' => 'Materia')
	);
	static $belongs_to = array(
	);
	
	static $alias_attribute = array(
	);
}
?>