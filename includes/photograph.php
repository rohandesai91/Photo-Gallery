<?php
require_once('database.php');

class photograph extends databaseobject {

	protected static $table_name = "photographs";
	public $id;
	public $filename;
	public $type;
	public $size;
	public $caption;

	private $temp;
	protected $upload_dir = "images";
	public $errors = array();

	protected $upload_errors = array(
	// http://www.php.net/manual/en/features.file-upload.errors.php
		UPLOAD_ERR_OK 				=> "No errors.",
		UPLOAD_ERR_INI_SIZE  		=> "Larger than upload_max_filesize.",
		UPLOAD_ERR_FORM_SIZE 		=> "Larger than form MAX_FILE_SIZE.",
		UPLOAD_ERR_PARTIAL 			=> "Partial upload.",
		UPLOAD_ERR_NO_FILE 			=> "No file.",
		UPLOAD_ERR_NO_TMP_DIR 		=> "No temporary directory.",
		UPLOAD_ERR_CANT_WRITE 		=> "Can't write to disk.",
		UPLOAD_ERR_EXTENSION 		=> "File upload stopped by extension."
	);

	// photograph class methods here
	// will be passing $_FILE[] as an argument to this function
	public function attach_file($file) {
		// Perform error checking
		if(!$file || empty($file) || !is_array($file)) {
			// nothing uploaded orwrong argument usage
			$this->errors[] = "No file uploaded";
			return false;
		} elseif ($file['error'] != 0) {
			$this->errors[] = $this->upload_errors[$file['error']];
			# code...
			return false;
		} else {

			// Set object attributes to the form parameters
			$this->temp = $file['tmp_name'];
			$this->filename = basename($file['name']);
			$this->type = $file['type'];
			$this->size = $file['size'];
			return true;
		}
	}

	public function save() {
		// new record won't have an id
		if(isset($this->id)) {
			// just update caption
			$this->update();
		} else {
			// make sure no errors

			// can't save if preexisting error
			if(!empty($this->errors)) { return false; }

			// make sure the caption is not too long
			if(strlen($this->caption) > 255) {
				$this->errors[] = "The caption can be only 255 characters long";
				return false;
			}

			// can't save without filename and temp location
			if(empty($this->filename) || empty($this->temp)) { 
				$this->errors[] = "The file location was not available";
				return false;
			}

			// Determine target location
			$target = "/wamp/www/photo_gallery/public/images/".$this->filename;

			// make sure file doesn't already exist
			if(file_exists($target)) {
				$this->errors[] = " The file {$this->filename} alerady exists";
			 	return false;
			}

			// Attemp to move the file
			if (move_uploaded_file($this->temp, $target)) {
				//echo "upload file if entered";
				// success
				// save corresponding entry to the database
				if($this->create()) {
					// we are done with temporary path
					//echo "here";
					unset($this->temp);
					return true;
				}
			} else {
				// file was not moved
				$this->errors[] = "File upload failed";
				return false;
			}			
		}
	}

	public function destroy() {
		if($this->delete()) {
			// then remove the file
			$target = "/wamp/www/photo_gallery/public/images/".$this->filename;
			return unlink($target) ? true : false ;
		} else {
			// deleting failed
			return false;
		}
	}

	public function size_as_text() {
		if($this->size < 1024) {
			return "{$this->size} bytes";
		} elseif($this->size < 1048576) {
			$size_kb = round($this->size/1024);
			return "{$size_kb} KB";
		} else {
			$size_mb = round($this->size/1048576, 1);
			return "{$size_mb} MB";
		}
	}

	public function comments() {
		return comment::find_comments($this->id);
	}

	// Common methods
	public static function find_all() {
		return self::find_by_sql("SELECT * FROM ".self::$table_name);
	}

	public static function find_by_id($id=0) {
		global $db;
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

	// public function save() {
	// 	// A new record wont have an id yet
	// 	return isset($this->id) ? $this->update() : $this->create();
	// }

	public function create() { 
		global $db;
		// normal procedure first insert statement and then escape value
		$sql = "INSERT INTO ".self::$table_name."(";
		$sql .="filename, type, size, caption";
		$sql .=") VALUES ('";
		$sql .=$db->escape_value($this->filename) ."', '";
		$sql .=$db->escape_value($this->type) ."', '";
		$sql .=$db->escape_value($this->size) ."', '";
		$sql .=$db->escape_value($this->caption) ."')";
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
		$sql .= "filename ='".$db->escape_value($this->filename) ."', ";
		$sql .= "type ='".$db->escape_value($this->type) ."', ";
		$sql .= "size ='".$db->escape_value($this->size) ."', ";
		$sql .= "caption ='".$db->escape_value($this->caption) ."' ";
		$sql .= "WHERE id=".$db->escape_value($this->id);
		$db->query($sql);
		return ($db->affected_rows() == 1) ? true : false;
	}

	public function delete() {
		global $db;
		// same delete from table where statement
		echo "delete func reached";
		$sql = "DELETE FROM ".self::$table_name." ";
		$sql .= "WHERE id=".$db->escape_value($this->id);
		$sql .= " LIMIT 1";
		$db->query($sql);
		return ($db->affected_rows() == 1) ? true : false;
	}

}



?>