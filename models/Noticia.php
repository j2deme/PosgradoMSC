<?php
class Noticia extends ActiveRecord\Model {
	static $table_name = 'Noticias';
	static $has_one = array(		
	);
	static $has_many = array(
	);
	static $belongs_to = array(
	);
	
	static $alias_attribute = array(
		"imagen" => "link_imagen"
	);
}
?>