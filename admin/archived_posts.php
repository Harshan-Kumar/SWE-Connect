<?php
include '../components/connect.php';  // Database connection

session_start();

// Fetch archived posts
$select_archived_posts = $conn->prepare("SELECT archived_posts.*, users.name AS user_name FROM `archived_posts` JOIN `users` ON archived_posts.user_id = users.id");
$select_archived_posts->execute();
$total_archived_posts = $select_archived_posts->rowCount();
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Archived Posts</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php' ?>

<section class="archived-posts">
   <h1 class="heading">Archived Posts</h1>

   <div class="posts-container">
      <?php if ($total_archived_posts > 0) {
         while ($fetch_post = $select_archived_posts->fetch(PDO::FETCH_ASSOC)) {
      ?>
      <div class="post-box">
         <h3><?= htmlspecialchars($fetch_post['title']); ?></h3>
         <p>By: <?= htmlspecialchars($fetch_post['name']); ?></p>
         <p>Category: <?= htmlspecialchars($fetch_post['category']); ?></p>
         <p>Date Archived: <?= htmlspecialchars($fetch_post['date']); ?></p>
      </div>
      <?php
         }
      } else {
         echo '<p class="empty">No archived posts found!</p>';
      }
      ?>
   </div>
</section>

<!-- Custom JS file link -->
<script src="../js/admin_script.js"></script>

</body>
</html>
