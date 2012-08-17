<?php
class GeneroST extends ActiveRecord\Model {
	static $table_name = 'EstadisticasGenero';
	static $has_one = array(		
	);
	static $has_many = array(
	);
	static $belongs_to = array(
	);
	static $alias_attribute = array(
		'candidatos' => 'aspirantes_hombres',
		'candidatas' => 'aspirantes_mujeres',
		'aceptados' => 'aceptados_hombres',
		'aceptadas' => 'aceptados_mujeres',
		'alumnos' => 'alumnos_hombres',
		'alumnas' => 'alumnos_mujeres',
		'exalumnos' => 'exalumnos_hombres',
		'exalumnas' => 'exalumnos_mujeres',
		'desertores' => 'desercion_hombres',
		'desertoras' => 'desercion_mujeres'
	);
}
?>