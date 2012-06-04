<?php
class Subtask extends ActiveRecord\Model {
	static $belongs_to = array(
		array('task'),
		array('assignation')
	);
	
	static $has_many = array(
		array('comments','order' => 'created asc, id asc')
	);
	
	static $alias_attribute = array(
		'start' 	=> 'start_date',
		'end' 	=> 'end_date',
		'assignation' => 'assignation_id'
	);
}
?>