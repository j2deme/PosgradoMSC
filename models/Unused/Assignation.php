<?php
class Assignation extends ActiveRecord\Model {
	static $belongs_to = array(
		array('district'),
		array('activity'),
		array('task')
	);
	static $has_many = array(
		array('subtasks', 'order' => 'start_date asc, end_date asc')
	);
	
	static $alias_attribute = array(
		'district' 	=> 'district_id',
		'activity' 	=> 'activity_id',
		'task'		=> 'task_id'
	);
}
?>