<?php
class Materia extends ActiveRecord\Model {
	static $table_name = 'Materias';
	static $has_one = array(
	);
	static $has_many = array(
	);
	static $belongs_to = array(
		array('linea','class_name' => 'LineaInvestigacion')
	);
	
	static $alias_attribute = array(
		"horas" => "horas_totales",
		"pdf" => "link_pdf"
	);
}
?>