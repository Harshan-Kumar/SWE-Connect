<?php
// home.php
include 'components/connect.php'; // Database connection

session_start();

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

include 'components/like_post.php'; // Functionality for liking posts

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Collaborative Knowledge Sharing Platform</title>

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; // Include the header ?>

<section class="home-grid">
   <div class="box-container">

      <!-- User Profile Information -->
      <div class="box">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
            $select_profile->execute([$user_id]);
            if ($select_profile->rowCount() > 0) {
               $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
               $count_user_comments = $conn->prepare("SELECT * FROM `comments` WHERE user_id = ?");
               $count_user_comments->execute([$user_id]);
               $total_user_comments = $count_user_comments->rowCount();
               $count_user_likes = $conn->prepare("SELECT * FROM `likes` WHERE user_id = ?");
               $count_user_likes->execute([$user_id]);
               $total_user_likes = $count_user_likes->rowCount();
         ?>
         <p> Welcome, <span><?= $fetch_profile['name']; ?></span></p>
         <p>Total Comments: <span><?= $total_user_comments; ?></span></p>
         <p>Posts Liked: <span><?= $total_user_likes; ?></span></p>
         <a href="update.php" class="btn">Update Profile</a>
         <div class="flex-btn">
            <a href="user_likes.php" class="option-btn">Likes</a>
            <a href="user_comments.php" class="option-btn">Comments</a>
            <a href="create_post.php" class="btn create-post-btn">Create Post</a> <!-- Adjusted class -->
         </div>
         <?php
            } else {
         ?>
            <p class="name">Please log in or register!</p>
            <div class="flex-btn">
               <a href="login.php" class="option-btn">Login</a>
               <a href="register.php" class="option-btn">Register</a>
            </div>
         <?php
            }
         ?>
      </div>

      <!-- Predefined Categories Section -->
      <div class="box">
         <p>Categories</p>
         <div class="flex-box">
            <a href="category.php?category=workshops" class="links">Workshops</a>
            <a href="category.php?category=placements" class="links">Placements</a>
            <a href="category.php?category=hackathons" class="links">Hackathons</a>
            <a href="category.php?category=projects" class="links">Projects</a>
            <a href="category.php?category=competitions" class="links">Competitions</a>
            <a href="category.php?category=internships" class="links">Internships</a>
            <a href="category.php?category=exams" class="links">Exams</a>
            <a href="all_category.php" class="btn">View All</a>
         </div>
      </div>

      <!-- Author Section -->
      <div class="box">
         <p>Authors</p>
         <div class="flex-box">
         <?php
            $select_authors = $conn->prepare("SELECT DISTINCT name FROM `admin` LIMIT 10");
            $select_authors->execute();
            if ($select_authors->rowCount() > 0) {
               while ($fetch_authors = $select_authors->fetch(PDO::FETCH_ASSOC)) {
         ?>
            <a href="author_posts.php?author=<?= $fetch_authors['name']; ?>" class="links"><?= $fetch_authors['name']; ?></a>
            <?php
            }
         } else {
            echo '<p class="empty">No authors yet!</p>';
         }
         ?>
         <a href="authors.php" class="btn">View All</a>
         </div>
      </div>

   </div>
</section>

<!-- Reference Books Button Section -->
<section class="reference-books-button" style="text-align: center; margin: 5rem 50rem;">
   <a href="upload_book.php" class="btn" style="font-size: 2rem; padding: 1rem 1.5rem; display: inline-block;">Reference Books</a>
</section>

<!-- Latest Posts Section -->
<section class="posts-container">
   <h1 class="heading">Latest Posts</h1>
   <div class="box-container">

      <?php
         $select_posts = $conn->prepare("SELECT * FROM `posts` WHERE status = ? LIMIT 6");
         $select_posts->execute(['active']);
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
         <input type="hidden" name="post_id" value="<?= $post_id; ?>">
         <div class="post-admin">
            <i class="fas fa-user"></i>
            <div>
               <a href="author_posts.php?author=<?= $fetch_posts['name']; ?>"><?= $fetch_posts['name']; ?></a>
               <div><?= $fetch_posts['date']; ?></div>
            </div>
         </div>

         <?php if ($fetch_posts['image'] != '') { ?>
            <img src="uploaded_img/<?= $fetch_posts['image']; ?>" class="post-image" alt="Post Image">
         <?php } ?>

         <div class="post-title"><?= $fetch_posts['title']; ?></div>
         <div class="post-content content-150"><?= $fetch_posts['content']; ?></div>
         <a href="view_post.php?post_id=<?= $post_id; ?>" class="inline-btn">Read More</a>
         <a href="category.php?category=<?= $fetch_posts['category']; ?>" class="post-cat"><i class="fas fa-tag"></i> <span><?= $fetch_posts['category']; ?></span></a>
         <div class="icons">
            <a href="view_post.php?post_id=<?= $post_id; ?>"><i class="fas fa-comment"></i><span>(<?= $total_post_comments; ?>)</span></a>
            <button type="submit" name="like_post"><i class="fas fa-heart" style="<?php if ($confirm_likes->rowCount() > 0) { echo 'color:var(--red);'; } ?>"></i><span>(<?= $total_post_likes; ?>)</span></button>
         </div>
      </form>
      <?php
         }
      } else {
         echo '<p class="empty">No posts added yet!</p>';
      }
      ?>
   </div>
   <div class="more-btn" style="text-align: center; margin-top:1rem;">
      <a href="posts.php" class="inline-btn">View All Posts</a>
   </div>
</section>

<?php include 'components/footer.php'; // Include the footer ?>

<script src="js/script.js"></script>
</body>
</html>
