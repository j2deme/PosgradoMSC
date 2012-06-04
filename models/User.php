<?php
class User extends ActiveRecord\Model {
	static $has_one = array(
		array('role')
	);
	static $belongs_to = array(
		array('district')
	);
	
	static $alias_attribute = array(
		'district' 	=> 'district_id',
		'role' 	=> 'role_id'
	);
}
?>