<?php
/**
 * Generic class
 * Simple Usage
 * $person = new generic();
 * $person->set('name','David');
 * $person->set('age','24');
 * $person->set('occupation','Programmer');
 * $person->unload('name');
 * $person->unload(array('age','occupation'));
 *
 * Database Usage
 * $query = 'SELECT name,age,occupation FROM users WHERE user_id = 1';
 * $result = mysql_query($query);
 * $row = mysql_fetch_assoc($result);
 * $user = new generic();
 * $user->load($row);
 * 
 * Session Usage
 * $_SESSION['person'] = $person->getAll();
 * $person = new generic();
 * $person->load($_SESSION['person']);
 */
class Generic {  
  var $vars;

  //Constructor
  function generic() {  }

  //Gets a value
  function get($var){
    return $this->vars[$var];
  }

  //Sets a key => value
  function set($key,$value){
    $this->vars[$key] = $value;
  }

  //Loads a key => value array into the class
  function load($array){
    if(is_array($array)){
      foreach($array as $key => $value){
        $this->vars[$key] = $value;
      }
    } elseif(is_object($array)){
    	//$this->vars = unserialize(serialize($array));
    	$object = get_object_vars($array);
		foreach($object as $key => $value){
	      $this->vars[$key] = $value;
	    }
    }
  }

  //Empties a specified setting or all of them
  function unload($vars = ''){
    if($vars){
      if(is_array($vars)){
        foreach($vars as $var){
          unset($this->vars[$var]);
        }
      } else {
        unset($this->vars[$vars]);
      }
    } else {
      $this->vars = array();
    }
  }

  // return the object as an array
  function getAll(){
    return $this->vars;
  }
}
?>