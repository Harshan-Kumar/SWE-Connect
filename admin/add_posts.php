<?php
// add_posts.php
include '../components/connect.php';

session_start();

// Ensure the user is logged in (admin or user)


$message = []; // Create a messages array to store status messages

if (isset($_POST['publish']) || isset($_POST['draft'])) {

    $name = $_POST['name'] ?? ''; // Added to avoid undefined index notice
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $title = $_POST['title'];
    $title = filter_var($title, FILTER_SANITIZE_STRING);
    $content = $_POST['content'];
    $content = filter_var($content, FILTER_SANITIZE_STRING);
    $category = $_POST['category'];
    $category = filter_var($category, FILTER_SANITIZE_STRING);
    $status = isset($_POST['publish']) ? 'active' : 'inactive';  // If publishing, set status to active, else inactive for drafts.

    $image = $_FILES['image']['name'];
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = '../uploaded_img/' . $image;

    // Check for image duplication
    $select_image = $conn->prepare("SELECT * FROM `posts` WHERE image = ? AND user_id = ?");
    $select_image->execute([$image, $user_id]);

    if (isset($image) && $image != '') {
        if ($select_image->rowCount() > 0) {
            $message[] = 'Image name repeated!';
        } elseif ($image_size > 2000000) {
            $message[] = 'Image size is too large!';
        } else {
            move_uploaded_file($image_tmp_name, $image_folder);
        }
    } else {
        $image = ''; // If no image is uploaded
    }

    if ($select_image->rowCount() > 0 && $image != '') {
        $message[] = 'Please rename your image!';
    } else {
        // Insert post or draft into database - supports both admin and user
        $insert_post = $conn->prepare("INSERT INTO `posts`(user_id, title, content, category, image, status) VALUES(?,?,?,?,?,?)");
        $insert_post->execute([$user_id, $title, $content, $category, $image, $status]);

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
    <title>Posts</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="../css/admin_style.css">

</head>

<body>

    <!-- Include the admin header only if an admin is logged in -->
  

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
                <option value="" selected disabled>-- Select Category* --</option>
                <option value="workshops">Workshops</option>
                <option value="placements">Placements</option>
                <option value="hackathons">Hackathons</option>
                <option value="projects">Projects</option>
                <option value="competitions">Competitions</option>
                <option value="internships">Internships</option>
                <option value="exams">Exams</option>
            </select>
            <p>Post Image</p>
            <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png, image/webp">
            <div class="flex-btn">
                <input type="submit" value="Publish Post" name="publish" class="btn">
                <input type="submit" value="Save Draft" name="draft" class="option-btn">
                <!-- Add Home Button -->
                <a href="../home.php" class="btn">Home</a>
            </div>
        </form>
    </section>

    <!-- Custom JS file link -->
    <script src="../js/admin_script.js"></script>

</body>

</html>    
