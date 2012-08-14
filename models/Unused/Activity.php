<?php
class Activity extends ActiveRecord\Model {
	static $has_many = array(
		array('assignations', 'group' => 'activity_id', 'order' => 'id asc'),
		array('tasks', 'through' => 'assignations', 'group' => 'name', 'order' => 'id asc')
	);
}
?>