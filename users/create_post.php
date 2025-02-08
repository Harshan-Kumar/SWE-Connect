<?php
// create_post.php
include 'components/connect.php';

session_start();

// Check if it's a user or admin logged in
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $user_id = null; // Admin doesn't have a `user_id`
    $is_admin = true;
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $admin_id = null; // User doesn't have an `admin_id`
    $is_admin = false;
} else {
    header('location:../login.php'); // Redirect to login if no user or admin is logged in
    exit();
}

$message = []; // Create a messages array to store status messages

if (isset($_POST['publish']) || isset($_POST['draft'])) {
    $title = filter_var(trim($_POST['title']), FILTER_SANITIZE_STRING);
    $content = filter_var(trim($_POST['content']), FILTER_SANITIZE_STRING);
    $category = filter_var(trim($_POST['category']), FILTER_SANITIZE_STRING);
    $status = isset($_POST['publish']) ? 'active' : 'inactive'; // Set status based on publish/draft

    // Image handling
    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/' . basename($image);

    // Check for image duplication only if an image is provided
    if ($image) {
        $select_image = $conn->prepare("SELECT * FROM `posts` WHERE image = ? AND (user_id = ? OR admin_id = ?)");
        $select_image->execute([$image, $user_id, $admin_id]);

        if ($image_size > 2000000) {
            $message[] = 'Image size is too large! Maximum size is 2MB.';
        } elseif ($select_image->rowCount() > 0) {
            $message[] = 'Image name is already used! Please rename your image.';
        } else {
            if (move_uploaded_file($image_tmp_name, $image_folder)) {
                $insert_post = $conn->prepare("
                    INSERT INTO `posts` (user_id, admin_id, title, content, category, image, status, name) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $insert_post->execute([
                    $user_id, 
                    $admin_id, 
                    $title, 
                    $content, 
                    $category, 
                    $image, 
                    $status, 
                    $is_admin ? 'Admin' : 'User'
                ]);
                $message[] = isset($_POST['publish']) ? 'Post published!' : 'Draft saved!';
            } else {
                $message[] = 'Failed to upload image. Please try again.';
            }
        }
    } else {
        // Insert post without an image
        $insert_post = $conn->prepare("
            INSERT INTO `posts` (user_id, admin_id, title, content, category, image, status, name) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insert_post->execute([
            $user_id, 
            $admin_id, 
            $title, 
            $content, 
            $category, 
            '', 
            $status, 
            $is_admin ? 'Admin' : 'User'
        ]);
        $message[] = isset($_POST['publish']) ? 'Post published!' : 'Draft saved!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Post</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="../css/admin_style.css">

    <style>
        /* Add your custom styles here */
    </style>
</head>
<body>

<section class="post-editor">
    <h1 class="heading">Add New Post</h1>

    <!-- Display messages if any -->
    <?php if (!empty($message)): ?>
        <div class="messages">
            <?php foreach ($message as $msg): ?>
                <p><?= $msg; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data">
        <p>Post Title <span>*</span></p>
        <input type="text" name="title" maxlength="100" required placeholder="Add post title" class="box">
        <p>Post Content <span>*</span></p>
        <textarea name="content" class="box" required maxlength="10000" placeholder="Write your content..." cols="30" rows="10"></textarea>
        <p>Post Category <span>*</span></p>
        <select name="category" class="box" required>
            <option value="" selected disabled>-- Select Category --</option>
            <option value="Workshops">Workshops</option>
            <option value="Placements">Placements</option>
            <option value="Hackathons">Hackathons</option>
            <option value="Projects">Projects</option>
            <option value="Competitions">Competitions</option>
            <option value="Internships">Internships</option>
            <option value="Exams">Exams</option>
        </select>
        <p>Post Image</p>
        <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png, image/webp">
        <div class="flex-btn">
            <input type="submit" value="Publish Post" name="publish" class="btn">
            <input type="submit" value="Save Draft" name="draft" class="option-btn">
            <a href="home.php" class="btn">Home</a>
        </div>
    </form>
</section>

<script src="../js/admin_script.js"></script>
</body>
</html>
