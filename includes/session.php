<?php
// A class for session for mainly managing users logging in and out

// do not store db objects in sessions use ids
class session {
	
	private $logged_in;
	public $user_id;
	public $message;

	function __construct() {
		session_start();
		$this->check_msg();
		$this->check_login();
		// if($this->logged_in) {
		// 	// actions to take if logged in
		// } else {
		// 	// actions to take if user in not logged in
		// }
	}

	public function is_logged_in() {
		return $this->logged_in;
	}

	public function login($user) {
		// database should find user based on username and password
		if($user) {
			$this->user_id = $_SESSION['user_id'] = $user->id;
			$this->logged_in = true;
		}
	}

	public function message($msg="") {
		if(!empty($msg)) {
			// set the message here
			$_SESSION['message'] = $msg;
		} else {
			return $this->message;
		}
	}

	public function logout() {
		unset($_SESSION['user_id']);
		unset($this->user_id);
		$this->logged_in = false;
	}

	private function check_login() {
		if(isset($_SESSION['user_id'])) {
			$this->user_id = $_SESSION['user_id'];
			$this->logged_in = true;
		} else {
			unset($this->user_id);
			$this->logged_in = false;
		}
	}

	private function check_msg() {
		if(isset($_SESSION['message'])) {
			$this->message = $_SESSION['message'];
			
			unset($_SESSION['message']);
		} else {
			
			$this->message = "";
		}
	}
}

$session = new session();
$message = $session->message();

?>