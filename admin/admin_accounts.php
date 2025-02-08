<?php
// admin_accounts.php
include '../components/connect.php';  // Database connection

session_start();

// Check if the user is logged in as admin


if (isset($_POST['delete_post'])) {
    $post_id = $_POST['post_id'];

    $delete_post = $conn->prepare("DELETE FROM `posts` WHERE id = ?");
    $delete_post->execute([$post_id]);
    $message[] = 'Post deleted!';
}

if (isset($_POST['delete_comment'])) {
    $comment_id = $_POST['comment_id'];

    $delete_comment = $conn->prepare("DELETE FROM `comments` WHERE id = ?");
    $delete_comment->execute([$comment_id]);
    $message[] = 'Comment deleted!';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Accounts</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php';  // Include the admin header ?>

<!-- Admin accounts section starts -->
<section class="admin-accounts">
    <h1 class="heading">Admin Accounts</h1>

    <div class="admin-container">
        <?php
        // Fetch the list of all admins from the database
        $select_admins = $conn->prepare("SELECT * FROM `admin`");
        $select_admins->execute();

        // Check if there are any admins
        if ($select_admins->rowCount() > 0) {
            while ($fetch_admin = $select_admins->fetch(PDO::FETCH_ASSOC)) {
        ?>
            <div class="admin-box">
                <h3><?= htmlspecialchars($fetch_admin['name']); ?></h3>
                <form action="" method="POST" class="delete-admin-form">
                    <input type="hidden" name="admin_id" value="<?= $fetch_admin['id']; ?>">
                    <button type="submit" name="delete_admin" class="delete-btn" onclick="return confirm('Are you sure you want to delete this admin?');">Delete Admin</button>
                </form>
            </div>
        <?php
            }
        } else {
            echo '<p class="empty">No admins found!</p>';
        }
        ?>
    </div>
</section>

<!-- Custom JS file link -->
<script src="../js/admin_script.js"></script>

</body>
</html>
