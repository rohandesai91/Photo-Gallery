<?php require_once("../../includes/initialize.php"); ?>
<?php if (!$session->is_logged_in()) { redirect_to("login.php"); } ?>

<?php

if(empty($_GET['id'])) {
	$session->message("No photo id provided");
	redirect_to('index.php');
}

$photo = photograph::find_by_id($_GET['id']);
if($photo && $photo->destroy()) {
	$session->message("Photo Deleted");
	redirect_to('list_photos.php');
} else {
	$session->message("Photo could not be deleted");
	redirect_to('list_photos.php');
}

if(isset($db)) { $db->close_connection(); }
?>