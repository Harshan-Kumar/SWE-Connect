<?php
//comments.php
include 'components/connect.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
    header('location:user_login.php');
}

if(isset($_POST['add_comment'])){

    $comment = $_POST['comment'];
    $comment = filter_var($comment, FILTER_SANITIZE_STRING);
    $post_id = $_POST['post_id'];

    $insert_comment = $conn->prepare("INSERT INTO `comments`(post_id, user_id, comment) VALUES(?, ?, ?)");
    $insert_comment->execute([$post_id, $user_id, $comment]);
    $message[] = 'Comment added!';
}
?>

<form action="" method="POST">
    <textarea name="comment" placeholder="Write your comment"></textarea>
    <input type="hidden" name="post_id" value="<?= $post_id; ?>">
    <input type="submit" name="add_comment" value="Add Comment">
</form>

<!-- Displaying comments -->
<?php
$select_comments = $conn->prepare("SELECT * FROM `comments` WHERE post_id = ?");
$select_comments->execute([$post_id]);
if($select_comments->rowCount() > 0){
    while($comment = $select_comments->fetch(PDO::FETCH_ASSOC)){
        echo "<div class='comment'>{$comment['comment']}</div>";
    }
}else{
    echo '<p>No comments yet!</p>';
}
?>
