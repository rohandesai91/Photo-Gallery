<?php 
require_once('config.php');

class mysqldb {

	private $conn;

	function __construct() {
		$this->open_connection();
	}

	public function open_connection() {
		$this->conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		if(mysqli_connect_errno()) {
			die("Connection Failed". 
				mysqli_connect_error() . "(" .
					mysqli_connect_errno() . ")" 
			);
		}
	}

	public function close_connection() {
		if(isset($this->conn)) {
			mysqli_close($this->conn);
			unset($this->conn);
		}
	}

	public function query($sql) {
		$result = mysqli_query($this->conn, $sql);
		$this->confirm_query($result);
		return $result; 
	}

	private function confirm_query($result) {
		if (!$result) {
			die("Database query failed");
		}
	}

	public function escape_value($string) {
		$escaped_string = mysqli_real_escape_string($this->conn, $string);
		return $escaped_string;
	}

	// Database Neutral functions

	public function fetch_array($result_set) {
		return mysqli_fetch_array($result_set);
	}

	public function num_rows($result_set) {
		return mysqli_num_rows($result_set);
	}

	public function insert_id() {
		// get last id inserted over the current db connection
		return mysqli_insert_id($this->conn);		
	}

	public function affected_rows() {
		// returns no of wors affected by last sql statement
		return mysqli_affected_rows($this->conn);
	}
}

$db = new mysqldb();
?>