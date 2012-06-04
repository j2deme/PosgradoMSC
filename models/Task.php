<?php
class Task extends ActiveRecord\Model {
	static $has_many = array(
		array('assignations'),
		//array('subtasks')
		array('subtasks', 'through' => 'assignations', 'group' => 'name', 'order' => 'id asc')
	);
}
?>