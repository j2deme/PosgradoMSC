<?php
class Posgrado extends ActiveRecord\Model {
	static $table_name = 'Posgrado';
	static $has_one = array(
		array('asesor', 'class_name' => 'Usuario'),
		array('linea', 'class_name' => 'LineaInvestigacion')		
	);
	static $has_many = array(
	);
	static $belongs_to = array(
	);
	
	static $alias_attribute = array(
		'nombre' => 'nombre_tesis',
		'asignacion' => 'fecha_asignacion',
		'fin' => 'fecha_fin_curso',
		'titulacion' => 'fecha_titulacion',
#		'linea' => 'linea_investigacion',
#		'tesis' => 'link_tesis',
#		'expo' => 'link_exposicion'
	);
}
?>