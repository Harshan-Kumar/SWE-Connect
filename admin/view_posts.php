<?php
include '../components/connect.php'; // Database connection

session_start();

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php'); // Redirect if not logged in
    exit;
}

$admin_id = $_SESSION['admin_id']; // Store the admin ID from session

// Handle post deletion
if (isset($_POST['delete'])) {
    $p_id = $_POST['post_id'];
    $p_id = filter_var($p_id, FILTER_SANITIZE_STRING);

    // Get the post details and image
    $delete_image = $conn->prepare("SELECT * FROM `posts` WHERE id = ?");
    $delete_image->execute([$p_id]);
    $fetch_delete_image = $delete_image->fetch(PDO::FETCH_ASSOC);

    // Delete the image file if it exists
    if ($fetch_delete_image['image'] != '') {
        unlink('../uploaded_img/' . $fetch_delete_image['image']);
    }

    // Delete the post
    $delete_post = $conn->prepare("DELETE FROM `posts` WHERE id = ?");
    $delete_post->execute([$p_id]);

    // Delete comments related to the post
    $delete_comments = $conn->prepare("DELETE FROM `comments` WHERE post_id = ?");
    $delete_comments->execute([$p_id]);

    $message[] = 'Post deleted successfully!';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="show-posts">
    <h1 class="heading">Your Posts</h1>

    <div class="box-container">
        <?php
        // Fetch posts for the admin
        $select_posts = $conn->prepare("SELECT * FROM `posts` WHERE user_id = ?");
        $select_posts->execute([$admin_id]);

        if ($select_posts->rowCount() > 0) {
            while ($fetch_posts = $select_posts->fetch(PDO::FETCH_ASSOC)) {
                $post_id = $fetch_posts['id'];

                // Get the number of comments for the post
                $count_post_comments = $conn->prepare("SELECT * FROM `comments` WHERE post_id = ?");
                $count_post_comments->execute([$post_id]);
                $total_post_comments = $count_post_comments->rowCount();

                // Get the number of likes for the post
                $count_post_likes = $conn->prepare("SELECT * FROM `likes` WHERE post_id = ?");
                $count_post_likes->execute([$post_id]);
                $total_post_likes = $count_post_likes->rowCount();

        ?>
        <form method="post" class="box">
            <input type="hidden" name="post_id" value="<?= $post_id; ?>">
            <?php if ($fetch_posts['image'] != '') { ?>
                <img src="../uploaded_img/<?= $fetch_posts['image']; ?>" class="image" alt="">
            <?php } ?>
            <div class="status" style="background-color:<?php if ($fetch_posts['status'] == 'active') { echo 'limegreen'; } else { echo 'coral'; }; ?>;">
                <?= $fetch_posts['status']; ?>
            </div>
            <div class="title"><?= $fetch_posts['title']; ?></div>
            <div class="posts-content"><?= $fetch_posts['content']; ?></div>
            <div class="icons">
                <div class="likes"><i class="fas fa-heart"></i><span><?= $total_post_likes; ?></span></div>
                <div class="comments"><i class="fas fa-comment"></i><span><?= $total_post_comments; ?></span></div>
            </div>
            <div class="flex-btn">
                <a href="edit_post.php?id=<?= $post_id; ?>" class="option-btn">Edit</a>
                <button type="submit" name="delete" class="delete-btn" onclick="return confirm('Delete this post?');">Delete</button>
            </div>
            <a href="read_post.php?post_id=<?= $post_id; ?>" class="btn">View Post</a>
        </form>
        <?php
            }
        } else {
            echo '<p class="empty">No posts added yet! <a href="add_posts.php" class="btn" style="margin-top:1.5rem;">Add Post</a></p>';
        }
        ?>
    </div>
</section>

<!-- Custom JS file link -->
<script src="../js/admin_script.js"></script>

</body>
</html>
