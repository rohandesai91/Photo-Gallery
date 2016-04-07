<?php require_once("../includes/initialize.php"); ?>
<?php
  if(empty($_GET['id'])) {
    $session->message("No photograph ID was provided.");
    redirect_to('index.php');
  }
  
  $photo = photograph::find_by_id($_GET['id']);
  if(!$photo) {
    $session->message("The photo could not be located.");
    redirect_to('index.php');
  }

  if(isset($_POST['submit'])) {
    $author = $_POST['author'];
    $body = $_POST['body'];
    echo $photo->id;

    $new_comment = comment::make($photo->id, $author, $body);
    

    if($new_comment && $new_comment->save()) {
      // comment saved
      // send notification
      $new_comment->send_notification();
      redirect_to("photo.php?id={$photo->id}");
    } else {
      // Failed
      $message = "Comment not saved due to error";
    }
  } else {
    $author = "";
    $body = "";
  }

  $comments = $photo->comments();
?>
<?php include('layouts/header.php');; ?>

<a href="index.php">&laquo; Back</a><br />
<br />

<div style="margin-left: 20px;">
  <img src="images/<?php echo $photo->filename; ?>" />
  <p><?php echo $photo->caption; ?></p>
</div>

<div id="comments">
  <?php foreach($comments as $comment): ?>
    <div class="comment" style="margin-bottom: 2em;">
      <div class="author">
        <?php echo htmlentities($comment->author); ?> wrote:
      </div>
      <div class="body">
        <?php echo strip_tags($comment->body, '<strong><em><p>'); ?>
      </div>
      <div class="meta-info" style="font-size: 0.8em;">

        <?php  $unixdatetime = strtotime($comment->created);
        echo strftime("%B %d, %Y at %I:%M %p", $unixdatetime); ?>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if(empty($comments)) { echo "No Comments."; } ?>
</div>


<div id="comment-form">
  <h3>New Comment</h3>
  <?php echo output_message($message); ?>
  <form action="photo.php?id=<?php echo $photo->id; ?>" method="post">
    <table>
      <tr>
        <td>Your name:</td>
        <td><input type="text" name="author" value="<?php echo $author; ?>" /></td>
      </tr>
      <tr>
        <td>Your comment:</td>
        <td><textarea name="body" cols="40" rows="8"><?php echo $body; ?></textarea></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td><input type="submit" name="submit" value="Submit Comment" /></td>
      </tr>
    </table>
  </form>
</div>

<?php include('layouts/footer.php'); ?>
