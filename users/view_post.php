<?php
//view_post.php
include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch user profile data
    $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
    $select_profile->execute([$user_id]);
    $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
} else {
    $user_id = '';
    $fetch_profile = null; // Set it to null if not logged in
}

include 'components/like_post.php';

$get_id = $_GET['post_id'];

if (isset($_POST['add_comment'])) {
    $admin_id = $_POST['user_id'];
    $admin_id = filter_var($admin_id, FILTER_SANITIZE_STRING);
    $user_name = $_POST['user_name'];
    $user_name = filter_var($user_name, FILTER_SANITIZE_STRING);
    $comment = $_POST['comment'];
    $comment = filter_var($comment, FILTER_SANITIZE_STRING);

    $verify_comment = $conn->prepare("SELECT * FROM `comments` WHERE post_id = ? AND admin_id = ? AND user_id = ? AND user_name = ? AND comment = ?");
    $verify_comment->execute([$get_id, $admin_id, $user_id, $user_name, $comment]);

    if ($verify_comment->rowCount() > 0) {
        $message[] = 'Comment already added!';
    } else {
        $insert_comment = $conn->prepare("INSERT INTO `comments`(post_id, admin_id, user_id, user_name, comment) VALUES(?,?,?,?,?)");
        $insert_comment->execute([$get_id, $admin_id, $user_id, $user_name, $comment]);
        $message[] = 'New comment added!';
    }
}

if (isset($_POST['edit_comment'])) {
    $edit_comment_id = $_POST['edit_comment_id'];
    $edit_comment_id = filter_var($edit_comment_id, FILTER_SANITIZE_STRING);
    $comment_edit_box = $_POST['comment_edit_box'];
    $comment_edit_box = filter_var($comment_edit_box, FILTER_SANITIZE_STRING);

    $verify_comment = $conn->prepare("SELECT * FROM `comments` WHERE comment = ? AND id = ?");
    $verify_comment->execute([$comment_edit_box, $edit_comment_id]);

    if ($verify_comment->rowCount() > 0) {
        $message[] = 'Comment already added!';
    } else {
        $update_comment = $conn->prepare("UPDATE `comments` SET comment = ? WHERE id = ?");
        $update_comment->execute([$comment_edit_box, $edit_comment_id]);
        $message[] = 'Your comment edited successfully!';
    }
}

if (isset($_POST['delete_comment'])) {
    $delete_comment_id = $_POST['comment_id'];
    $delete_comment_id = filter_var($delete_comment_id, FILTER_SANITIZE_STRING);
    $delete_comment = $conn->prepare("DELETE FROM `comments` WHERE id = ?");
    $delete_comment->execute([$delete_comment_id]);
    $message[] = 'Comment deleted successfully!';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Post</title>

    <!-- Font Awesome CDN link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS file link  -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Header Section Starts  -->
<?php include 'components/user_header.php'; ?>
<!-- Header Section Ends -->

<?php
if (isset($_POST['open_edit_box'])) {
    $comment_id = $_POST['comment_id'];
    $comment_id = filter_var($comment_id, FILTER_SANITIZE_STRING);
?>
    <section class="comment-edit-form">
        <p>Edit your comment</p>
        <?php
        $select_edit_comment = $conn->prepare("SELECT * FROM `comments` WHERE id = ?");
        $select_edit_comment->execute([$comment_id]);
        $fetch_edit_comment = $select_edit_comment->fetch(PDO::FETCH_ASSOC);
        ?>
        <form action="" method="POST">
            <input type="hidden" name="edit_comment_id" value="<?= htmlspecialchars($comment_id); ?>">
            <textarea name="comment_edit_box" required cols="30" rows="10" placeholder="Please enter your comment"><?= htmlspecialchars($fetch_edit_comment['comment']); ?></textarea>
            <button type="submit" class="inline-btn" name="edit_comment">Edit Comment</button>
            <div class="inline-option-btn" onclick="window.location.href = 'view_post.php?post_id=<?= htmlspecialchars($get_id); ?>';">Cancel Edit</div>
        </form>
    </section>
<?php
}
?>

<section class="posts-container" style="padding-bottom: 0;">

    <div class="box-container">

        <?php
        $select_posts = $conn->prepare("SELECT * FROM `posts` WHERE status = ? AND id = ?");
        $select_posts->execute(['active', $get_id]);
        if ($select_posts->rowCount() > 0) {
            while ($fetch_posts = $select_posts->fetch(PDO::FETCH_ASSOC)) {

                $post_id = $fetch_posts['id'];

                $count_post_comments = $conn->prepare("SELECT * FROM `comments` WHERE post_id = ?");
                $count_post_comments->execute([$post_id]);
                $total_post_comments = $count_post_comments->rowCount();

                $count_post_likes = $conn->prepare("SELECT * FROM `likes` WHERE post_id = ?");
                $count_post_likes->execute([$post_id]);
                $total_post_likes = $count_post_likes->rowCount();

                $confirm_likes = $conn->prepare("SELECT * FROM `likes` WHERE user_id = ? AND post_id = ?");
                $confirm_likes->execute([$user_id, $post_id]);
        ?>
                <form class="box" method="post">
                    <input type="hidden" name="post_id" value="<?= htmlspecialchars($post_id); ?>">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($fetch_posts['user_id']); ?>">
                    <div class="post-admin">
                        <i class="fas fa-user"></i>
                        <div>
                            <a href="author_posts.php?author=<?= htmlspecialchars($fetch_posts['name']); ?>"><?= htmlspecialchars($fetch_posts['name']); ?></a>
                            <div><?= htmlspecialchars($fetch_posts['date']); ?></div>
                        </div>
                    </div>

                    <?php
                    if ($fetch_posts['image'] != '' && file_exists("uploaded_img/{$fetch_posts['image']}")) {  
                    ?>
                        <img src="uploaded_img/<?= htmlspecialchars($fetch_posts['image']); ?>" class="post-image" alt="">
                    <?php
                    } else {
                        echo '<p>No image available for this post.</p>'; // Optional: fallback message
                    }
                    ?>
                    <div class="post-title"><?= htmlspecialchars($fetch_posts['title']); ?></div>
                    <div class="post-content"><?= htmlspecialchars($fetch_posts['content']); ?></div>
                    <div class="icons">
                        <div><i class="fas fa-comment"></i><span>(<?= $total_post_comments; ?>)</span></div>
                        <button type="submit" name="like_post"><i class="fas fa-heart" style="<?php if ($confirm_likes->rowCount() > 0) { echo 'color:var(--red);'; } ?>"></i><span>(<?= $total_post_likes; ?>)</span></button>
                    </div>

                </form>
        <?php
            }
        } else {
            echo '<p class="empty">No posts found!</p>';
        }
        ?>
    </div>

</section>

<section class="comments-container">

    <p class="comment-title">Add Comment</p>
    <?php if ($user_id != ''): ?>
        <form action="" method="post" class="add-comment">
            <?php
            $select_admin_id = $conn->prepare("SELECT * FROM `posts` WHERE id = ?");
            $select_admin_id->execute([$get_id]);
            $fetch_admin_id = $select_admin_id->fetch(PDO::FETCH_ASSOC);
            ?>
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($fetch_admin_id['user_id']); ?>">
            <input type="hidden" name="user_name" value="<?= htmlspecialchars($fetch_profile['name']); ?>">
            <p class="user"><i class="fas fa-user"></i><a href="update.php"><?= htmlspecialchars($fetch_profile['name']); ?></a></p>
            <textarea name="comment" maxlength="1000" class="comment-box" cols="30" rows="10" placeholder="Write your comment" required></textarea>
            <input type="submit" value="Add Comment" class="inline-btn" name="add_comment">
        </form>
    <?php else: ?>
        <div class="add-comment">
            <p>Please login to add or edit your comment</p>
            <a href="login.php" class="inline-btn">Login Now</a>
        </div>
    <?php endif; ?>

    <p class="comment-title">Post Comments</p>
    <div class="user-comments-container">
        <?php
        $select_comments = $conn->prepare("SELECT * FROM `comments` WHERE post_id = ?");
        $select_comments->execute([$get_id]);
        if ($select_comments->rowCount() > 0) {
            while ($fetch_comments = $select_comments->fetch(PDO::FETCH_ASSOC)) {
                ?>
                <div class="show-comments" style="<?= $fetch_comments['user_id'] == $user_id ? 'order:-1;' : ''; ?>">
                    <div class="comment-user">
                        <i class="fas fa-user"></i>
                        <div>
                            <span><?= htmlspecialchars($fetch_comments['user_name']); ?></span>
                            <div><?= htmlspecialchars($fetch_comments['date']); ?></div>
                        </div>
                    </div>
                    <div class="comment-box" style="<?= $fetch_comments['user_id'] == $user_id ? 'color:var(--white); background:var(--black);' : ''; ?>"><?= htmlspecialchars($fetch_comments['comment']); ?></div>
                    <?php if ($fetch_comments['user_id'] == $user_id): ?>
                        <form action="" method="POST">
                            <input type="hidden" name="comment_id" value="<?= htmlspecialchars($fetch_comments['id']); ?>">
                            <button type="submit" class="inline-option-btn" name="open_edit_box">Edit Comment</button>
                            <button type="submit" class="inline-delete-btn" name="delete_comment" onclick="return confirm('Delete this comment?');">Delete Comment</button>
                        </form>
                    <?php endif; ?>
                </div>
                <?php
            }
        } else {
            echo '<p class="empty">No comments added yet!</p>';
        }
        ?>
    </div>
</section>

<?php include 'components/footer.php'; ?>

<!-- Custom JS file link -->
<script src="js/script.js"></script>

</body>
</html>
