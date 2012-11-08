<?php
class Docente extends ActiveRecord\Model {
	static $table_name = 'Docentes';
	static $has_one = array(
		array('nivel_sni', 'class_name' => 'SNI'),
		array('promep', 'class_name' => 'PROMEP')		
	);
	static $has_many = array(
//		array('tesistas', 'class_name' => 'Usuario')
	);
	static $belongs_to = array(
	);
	
	static $alias_attribute = array(
		'ptc' => 'tiempo_completo',
		'sni' => 'nivel_sni' 
	);
}
?>