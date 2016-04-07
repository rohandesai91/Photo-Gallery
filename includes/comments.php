<?php
require_once('database.php');

class comment extends databaseobject {

	protected static $table_name = "comments";
	public $id;
	public $photo_id;
	public $body;
	public $author;
	public $created;

	public static function make($pid, $author="Anonymous", $body="") {
		if(!empty($pid) && !empty($author) && !empty($body)) {
		$comment = new comment();
		$comment->photo_id = (int)$pid;
		$comment->author = $author;
		$comment->body = $body;
		$comment->created = strftime("%Y/%m/%d %H:%M:%S", time());
		return $comment;
		} else {
			return false;
		}

	}

	public static function find_comments($photo_id=0) {
		global $db;
		$sql = "SELECT * FROM ".self::$table_name;
		$sql .= " WHERE photo_id = ".$db->escape_value($photo_id);
		$sql .= " ORDER BY created ASC";
		return self::find_by_sql($sql);
	}

	public function send_notification() {

		$account="rohandesai91@outlook.com";
		$password="p@r1neet1";
		$to="rohandesai911@gmail.com";
		$from="rohandesai91@outlook.com";
		$from_name="Photo_Gallery";
		$msg="<strong> A new comment added to photo</strong>"; // HTML message
		$subject="New Comment";

		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->CharSet = 'UTF-8';
		$mail->Host = "smtp.live.com";
		$mail->SMTPDebug = 1;
		$mail->SMTPAuth= true;
		$mail->Port = 587;
		$mail->Username= $account;
		$mail->Password= $password;
		$mail->SMTPSecure = 'tsl';
		$mail->From = $from;
		$mail->FromName= $from_name;
		$mail->addAddress($to);
		$mail->isHTML(true);
		$mail->Subject = $subject;
		$mail->Body =<<<EMAILBODY

A new comment has been received in the Photo Gallery.

By, {$this->author} wrote:

{$this->body}

EMAILBODY;
		

		if(!$mail->send()){
		 echo "Mailer Error: " . $mail->ErrorInfo;
		}else{
		 echo "E-Mail has been sent";
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
		echo "here in save".$this->id;
		return isset($this->id) ? $this->update() : $this->create();
	}

	public function create() { 
		global $db;
		// normal procedure first insert statement and then escape value
		$sql = "INSERT INTO ".self::$table_name."(";
		$sql .="photo_id, created, author, body";
		$sql .=") VALUES ('";
		$sql .=$db->escape_value($this->photo_id) ."', '";
		$sql .=$db->escape_value($this->created) ."', '";
		$sql .=$db->escape_value($this->author) ."', '";
		$sql .=$db->escape_value($this->body) ."')";
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
		$sql .= "photo_id ='".$db->escape_value($this->photo_id) ."', ";
		$sql .= "created ='".$db->escape_value($this->created) ."', ";
		$sql .= "author ='".$db->escape_value($this->author) ."', ";
		$sql .= "body ='".$db->escape_value($this->body) ."' ";
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