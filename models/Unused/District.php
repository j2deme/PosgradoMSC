<?php
class District extends ActiveRecord\Model {
	static $has_many = array(
		array('assignations'),
		array('activities', 'through' => 'assignations', 'group' => 'name')
	);
}
?>