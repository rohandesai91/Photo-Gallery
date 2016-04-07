<?php require_once("../includes/initialize.php"); ?>
<?php

	// 1. the current page no
	$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;

	// 2. records per page
	$per_page = 3;

	// 3, total record count ($total_count)
	$total = photograph::count_all();
	
	// Find all photos
	//$photos = photograph::find_all();
	// Use pagination instead of that
	$pagination = new pagination($page, $per_page, $total);

	// User sql statement to find all the records for the page
	$sql = "SELECT * FROM photographs ";
	$sql .= "LIMIT {$per_page} ";
	$sql .= "OFFSET {$pagination->offset()}";
	$photos = photograph::find_by_sql($sql);
?>

<?php include('layouts/header.php'); ?>

<?php foreach($photos as $photo): ?>
  <div style="float: left; margin-left: 20px;">
		<a href="photo.php?id=<?php echo $photo->id; ?>">
			<img src="images/<?php echo $photo->filename; ?>" width="200" />
		</a>
    <p><?php echo $photo->caption; ?></p>
  </div>
<?php endforeach; ?>

	<div id="pagination" style="clear: both;">
	<?php
	if($pagination->total_pages() > 1) {

		if($pagination->has_previous_page()) {
			echo "<a href=\"index.php?page=";
			echo $pagination->previous_page();
			echo "\"> &laquo; Previous </a>";
		}

		for($i=1; $i <= $pagination->total_pages(); $i++) {
			if($i == $page) {
				echo " <span class=\"selected\">{$i}</span> ";
			} else {
				echo " <a href=\"index.php?page={$i}\">{$i}</a> "; 
			}
		}


		if($pagination->has_next_page()) {
			echo "<a href=\"index.php?page=";
			echo $pagination->next_page();
			echo "\"> Next &raquo; </a>";
		}
	}

	?>

	</div>


<?php include('layouts/footer.php'); ?>
