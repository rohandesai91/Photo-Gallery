<?php
require_once('database.php');

class user extends databaseobject {

	protected static $table_name = "users";
	public $id;
	public $username;
	public $password;
	public $first_name;
	public $last_name;

	public static function authenticate($username="", $password="") {

	    global $db;
	    $username = $db->escape_value($username);
	    $password = $db->escape_value($password);

	    $sql  = "SELECT * FROM users ";
	    $sql .= "WHERE username = '{$username}' ";
	    $sql .= "AND password = '{$password}' ";
	    $sql .= "LIMIT 1";
	    
	    $result_array = self::find_by_sql($sql);
			return !empty($result_array) ? array_shift($result_array) : false;
	}

	public function full_name() {
		if(isset($this->first_name) && isset($this->last_name)) {
			return $this->first_name." ".$this->last_name;
		} else {
			return " ";
		}
	}

	// Common methods
	public static function find_all() {
		return self::find_by_sql("SELECT * FROM ".self::$table_name);
	}

	public static function find_by_id($id=0) {
		$result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE id={$id} LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
	}

	public static function find_by_sql($sql="") {
		global $db;
		$result_set = $db->query($sql);
		$object_array = array();
		while ($row = $db->fetch_array($result_set)) {
			$object_array[] = self:: instantiate($row);
			# code...
		}
		return $object_array;
	}

	public static function count_all() {
		global $db;
		$sql = "SELECT COUNT(*) FROM ".self::$table_name;
		$result_set = $db->query($sql);
		$row = $db->fetch_array($result_set);
		return array_shift($row);
	}

	private static function instantiate($record) {
		// Could check that $record exists and is an array
		// Simple long-form approach
		$obj = new self;
		// $obj->id 			= $record['id'];
		// $obj->username 		= $record['username'];
		// $obj->password 		= $record['password'];
		// $obj->first_name	= $record['first_name'];
		// $obj->last_name 	= $record['last_name'];

		// More Dynamic, short form approach
		foreach($record as $attribute=>$value){
		  if($obj->has_attribute($attribute)) {
		    $obj->$attribute = $value;
		  }
		}
		return $obj;
	}
	
	private function has_attribute($attribute) {
	  // get_object_vars returns an associative array with all attributes 
	  // (incl. private ones!) as the keys and their current values as the value
	  $object_vars = get_object_vars($this);
	  // We don't care about the value, we just want to know if the key exists
	  // Will return true or false
	  return array_key_exists($attribute, $object_vars);
	}

	public function save() {
		// A new record wont have an id yet
		return isset($this->id) ? $this->update() : $this->create();
	}

	public function create() { 
		global $db;
		// normal procedure first insert statement and then escape value
		$sql = "INSERT INTO ".self::$table_name."(";
		$sql .="username, password, first_name, last_name";
		$sql .=") VALUES ('";
		$sql .=$db->escape_value($this->username) ."', '";
		$sql .=$db->escape_value($this->password) ."', '";
		$sql .=$db->escape_value($this->first_name) ."', '";
		$sql .=$db->escape_value($this->last_name) ."')";
		if($db->query($sql)) {
			$this->id = $db->insert_id();
			return true;
		} else {
			return false;
		}
	}

	public function update() {
		global $db;
		// normal update table set command
		$sql = "UPDATE ".self::$table_name." SET ";
		$sql .= "username ='".$db->escape_value($this->username) ."', ";
		$sql .= "password ='".$db->escape_value($this->password) ."', ";
		$sql .= "first_name ='".$db->escape_value($this->first_name) ."', ";
		$sql .= "last_name ='".$db->escape_value($this->last_name) ."' ";
		$sql .= "WHERE id=".$db->escape_value($this->id);
		$db->query($sql);
		return ($db->affected_rows() == 1) ? true : false;
	}

	public function delete() {
		global $db;
		// same delete from table where statement

		$sql = "DELETE FROM ".self::$table_name." ";
		$sql .= "WHERE id=".$db->escape_value($this->id);
		$sql .= " LIMIT 1";
		$db->query($sql);
		return ($db->affected_rows() == 1) ? true : false;
	}

}

?>