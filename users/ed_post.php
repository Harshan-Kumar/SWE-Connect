<?php
//ed_post.php
include 'components/connect.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:../login.php');
    exit();
}

// Initialize message array
$message = [];

// Check if post ID is present
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $message[] = 'Post ID is missing!';
}

// Proceed if ID is present
if (isset($_GET['id'])) {
    $post_id = $_GET['id'];

    if (isset($_POST['save'])) {
        $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
        $content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);
        $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
        $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);

        // Update the post in the database
        $update_post = $conn->prepare("UPDATE `posts` SET title = ?, content = ?, category = ?, status = ? WHERE id = ?");
        $update_post->execute([$title, $content, $category, $status, $post_id]);

        $message[] = 'Post updated!';

        // Handle image upload
        $old_image = $_POST['old_image'];
        $image = $_FILES['image']['name'];
        $image_size = $_FILES['image']['size'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_folder = '../uploaded_img/' . $image;

        if (!empty($image)) {
            if ($image_size > 2000000) {
                $message[] = 'Image size is too large!';
            } else {
                move_uploaded_file($image_tmp_name, $image_folder);
                $update_image = $conn->prepare("UPDATE `posts` SET image = ? WHERE id = ?");
                $update_image->execute([$image, $post_id]);
                if ($old_image != $image && $old_image != '') {
                    unlink('../uploaded_img/' . $old_image);
                }
                $message[] = 'Image updated!';
            }
        }
    }

    // Deleting image
    if (isset($_POST['delete_image'])) {
        $empty_image = '';
        $delete_image = $conn->prepare("SELECT * FROM `posts` WHERE id = ?");
        $delete_image->execute([$post_id]);
        $fetch_delete_image = $delete_image->fetch(PDO::FETCH_ASSOC);

        if ($fetch_delete_image['image'] != '') {
            unlink('../uploaded_img/' . $fetch_delete_image['image']);
        }
        $unset_image = $conn->prepare("UPDATE `posts` SET image = ? WHERE id = ?");
        $unset_image->execute([$empty_image, $post_id]);
        $message[] = 'Image deleted successfully!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<section class="post-editor">
    <h1 class="heading">Edit Post</h1>

    <?php if (!empty($message)): ?>
        <div class="messages">
            <?php foreach ($message as $msg): ?>
                <p><?= htmlspecialchars($msg); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php
    if (isset($post_id)) {
        $select_posts = $conn->prepare("SELECT * FROM `posts` WHERE id = ?");
        $select_posts->execute([$post_id]);
        if ($select_posts->rowCount() > 0) {
            while ($fetch_posts = $select_posts->fetch(PDO::FETCH_ASSOC)) {
    ?>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="old_image" value="<?= htmlspecialchars($fetch_posts['image']); ?>">
        <input type="hidden" name="post_id" value="<?= htmlspecialchars($fetch_posts['id']); ?>">
        <p>Post Status <span>*</span></p>
        <select name="status" class="box" required>
            <option value="<?= htmlspecialchars($fetch_posts['status']); ?>" selected><?= htmlspecialchars($fetch_posts['status']); ?></option>
            <option value="active">Active</option>
            <option value="deactive">Deactive</option>
        </select>
        <p>Post Title <span>*</span></p>
        <input type="text" name="title" maxlength="100" required placeholder="Add post title" class="box" value="<?= htmlspecialchars($fetch_posts['title']); ?>">
        <p>Post Content <span>*</span></p>
        <textarea name="content" class="box" required maxlength="10000" placeholder="Write your content..." cols="30" rows="10"><?= htmlspecialchars($fetch_posts['content']); ?></textarea>
        <p>Post Category <span>*</span></p>
        <select name="category" class="box" required>
            <option value="<?= htmlspecialchars($fetch_posts['category']); ?>" selected><?= htmlspecialchars($fetch_posts['category']); ?></option>
            <option value="nature">Nature</option>
            <option value="education">Education</option>
            <option value="technology">Technology</option>
            <option value="entertainment">Entertainment</option>
            <!-- Add other categories as needed -->
        </select>
        <p>Post Image</p>
        <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png, image/webp">
        <?php if ($fetch_posts['image'] != ''): ?>
            <img src="../uploaded_img/<?= htmlspecialchars($fetch_posts['image']); ?>" class="image" alt="">
            <input type="submit" value="Delete Image" class="inline-delete-btn" name="delete_image">
        <?php endif; ?>
        <div class="flex-btn">
            <input type="submit" value="Save Post" name="save" class="btn">
            <a href="../home.php" class="option-btn">Home</a>
        </div>
    </form>
    <?php
            }
        } else {
            echo '<p class="empty">No posts found!</p>';
            echo '<a href="../home.php" class="option-btn">Go to Home</a>';
        }
    }
    ?>
</section>

<!-- Custom JS file link -->
<script src="../js/admin_script.js"></script>
</body>
</html>
