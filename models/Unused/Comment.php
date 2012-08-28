<?php
class Comment extends ActiveRecord\Model {
	static $belongs_to = array(
		array('subtask')
	);
	
	static $alias_attribute = array(
		'subtask'	=> 'subtask_id',
		'user'		=> 'user_id',
		'body'		=> 'comment'
	);
}
?>