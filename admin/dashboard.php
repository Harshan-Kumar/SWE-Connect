<?php
// dashboard.php
include '../components/connect.php';  // Database connection

session_start();

// Ensure the user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
   header('Location: admin_login.php');  // Redirect to login if not logged in as admin
   exit();
}

// Check for deletion request
if (isset($_POST['delete_post'])) {
   $post_id = $_POST['post_id'];
   $post_id = filter_var($post_id, FILTER_SANITIZE_STRING);
   
   // Prepare and execute the deletion query
   $delete_post = $conn->prepare("DELETE FROM `posts` WHERE id = ?");
   $delete_post->execute([$post_id]);

   if ($delete_post) {
      $message[] = 'Post deleted successfully!';
   } else {
      $message[] = 'Failed to delete post!';
   }
}

// Fetch user posts
$select_user_posts = $conn->prepare("SELECT posts.*, users.name AS user_name FROM `posts` JOIN `users` ON posts.user_id = users.id");
$select_user_posts->execute();
$total_user_posts = $select_user_posts->rowCount();

// Fetch total data for dashboard boxes
$total_posts = $conn->query("SELECT COUNT(*) FROM `posts`")->fetchColumn();
$active_posts = $conn->query("SELECT COUNT(*) FROM `posts` WHERE status = 'active'")->fetchColumn();
$deactive_posts = $conn->query("SELECT COUNT(*) FROM `posts` WHERE status = 'deactive'")->fetchColumn();
$total_users = $conn->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
$total_comments = $conn->query("SELECT COUNT(*) FROM `comments`")->fetchColumn();
$total_likes = $conn->query("SELECT COUNT(*) FROM `likes`")->fetchColumn();
$total_archived_posts = $conn->query("SELECT COUNT(*) FROM `archived_posts`")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Dashboard</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php' ?>

<!-- Admin dashboard section starts -->
<section class="dashboard">
   <h1 class="heading">Dashboard</h1>

   <div class="box-container">
      <div class="box">
         <h3>Welcome!</h3>
         <a href="update_profile.php" class="btn">Update Profile</a>
      </div>
      <div class="box">
         <h3><?= $total_posts; ?></h3>
         <p>Total Posts</p>
         <a href="view.php" class="btn">View All Posts</a>
      </div>
      <div class="box">
         <h3><?= $total_users; ?></h3>
         <p>User Accounts</p>
         <a href="users_accounts.php" class="btn">View Users</a>
      </div>
      <div class="box">
         <h3><?= $total_archived_posts; ?></h3>
         <p>Archived Posts</p>
         <a href="archived_posts.php" class="btn">View Archived Posts</a>
      </div>
   </div>
</section>

<!-- Reference Books Section -->
<section class="reference-books">
   <h1 class="heading">Reference Books</h1>
   <div class="box-container">
      <div class="box">
         <h3>Manage Books</h3>
         <p>View or delete uploaded books</p>
         <a href="reference_books.php" class="btn">Go to Books</a>
      </div>
   </div>
</section>

<!-- User Posts Section -->
<section class="user-posts">
   <h1 class="heading">All User Posts</h1>
   <div class="user-posts-container">
      <?php if ($total_user_posts > 0) {
         while ($fetch_post = $select_user_posts->fetch(PDO::FETCH_ASSOC)) {
      ?>
      <div class="post-box">
         <h3><?= htmlspecialchars($fetch_post['title']); ?></h3>
         <p>By: <?= htmlspecialchars($fetch_post['user_name']); ?></p>
         <form action="" method="POST" class="delete-post-form">
            <input type="hidden" name="post_id" value="<?= $fetch_post['id']; ?>">
            <button type="submit" name="delete_post" class="delete-btn" onclick="return confirm('Are you sure you want to delete this post?');">Delete Post</button>
         </form>
      </div>
      <?php
         }
      } else {
         echo '<p class="empty">No posts found!</p>';
      }
      ?>
   </div>
</section>

<!-- Custom JS file link -->
<script src="../js/admin_script.js"></script>

</body>
</html>
