<?php
include '../components/connect.php'; // Database connection

session_start();

// Ensure the user or admin is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_SESSION['admin_id'])) {
    $user_id = $_SESSION['admin_id'];  // Use admin ID if logged in as admin
} else {
    $user_id = ''; // Set to empty if not logged in
}

// Fetch posts from the database
$select_posts = $conn->prepare("SELECT id, name, date, image, title, content, category FROM `posts` WHERE status = ? LIMIT 6");
$select_posts->execute(['active']);
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

    <!-- Custom Styles -->
    <style>
        .box-container {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            justify-content: center;
        }

        .box {
            background-color: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 350px;
            width: 100%;
            position: relative;
        }

        .box img.post-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }

        .post-admin {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .post-admin i {
            font-size: 2rem;
            color: #333;
        }

        .post-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .post-content {
            font-size: 1rem;
            color: #666;
            margin-bottom: 1rem;
            height: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .post-cat {
            display: inline-block;
            margin-bottom: 1rem;
            color: #333;
            font-size: 0.9rem;
            text-decoration: none;
            background-color: #f5f5f5;
            padding: 0.5rem;
            border-radius: 5px;
        }

        .post-cat i {
            margin-right: 0.5rem;
        }

        .icons {
            display: flex;
            justify-content: space-around;
            margin-bottom: 1rem;
        }

        .icons a,
        .icons button {
            background: none;
            border: none;
            color: #333;
            font-size: 1.2rem;
            cursor: pointer;
        }

        .icons a:hover, .icons button:hover {
            color: #555;
        }

        .inline-btn {
            text-decoration: none;
            background-color: #3498db;
            color: #fff;
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            margin-top: 1rem;
            display: inline-block;
        }

        .inline-btn:hover {
            background-color: #2980b9;
        }

        .flex-btn {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .option-btn, .delete-btn, .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
        }

        .option-btn {
            background-color: #3498db;
            color: #fff;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: #fff;
        }

        .btn {
            background-color: #2ecc71;
            color: #fff;
        }

        .option-btn:hover {
            background-color: #2980b9;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .btn:hover {
            background-color: #27ae60;
        }

        @media (max-width: 768px) {
            .box-container {
                flex-direction: column;
            }
        }
    </style>

</head>
<body>

<div class="box-container">
    <?php
    if ($select_posts->rowCount() > 0) {
        while ($fetch_posts = $select_posts->fetch(PDO::FETCH_ASSOC)) {
            $post_id = $fetch_posts['id'];

            // Fetch comment and like counts
            $count_post_comments = $conn->prepare("SELECT COUNT(*) FROM `comments` WHERE post_id = ?");
            $count_post_comments->execute([$post_id]);
            $total_post_comments = $count_post_comments->fetchColumn();

            $count_post_likes = $conn->prepare("SELECT COUNT(*) FROM `likes` WHERE post_id = ?");
            $count_post_likes->execute([$post_id]);
            $total_post_likes = $count_post_likes->fetchColumn();

            // Check if the user or admin has liked the post
            $confirm_likes = $conn->prepare("SELECT COUNT(*) FROM `likes` WHERE user_id = ? AND post_id = ?");
            $confirm_likes->execute([$user_id, $post_id]);
            $user_liked = $confirm_likes->fetchColumn() > 0;
    ?>
    <form class="box" method="post">
        <input type="hidden" name="post_id" value="<?= htmlspecialchars($post_id); ?>">

        <div class="post-admin">
            <i class="fas fa-user"></i>
            <div>
                <a href="../author_posts.php?author=<?= urlencode($fetch_posts['name']); ?>">
                    <?= htmlspecialchars($fetch_posts['name']); ?>
                </a>
                <div><?= htmlspecialchars($fetch_posts['date']); ?></div>
            </div>
        </div>

        <?php if ($fetch_posts['image'] != '') { ?>
        <img src="../uploaded_img/<?= htmlspecialchars($fetch_posts['image']); ?>" class="post-image" alt="Post Image">
        <?php } ?>

        <div class="post-title"><?= htmlspecialchars($fetch_posts['title']); ?></div>
        <div class="post-content"><?= htmlspecialchars($fetch_posts['content']); ?></div>

        <a href="../view_posts.php?post_id=<?= urlencode($post_id); ?>" class="inline-btn">Read More</a>
        <a href="category.php?category=<?= urlencode($fetch_posts['category']); ?>" class="post-cat">
            <i class="fas fa-tag"></i> 
            <span><?= htmlspecialchars($fetch_posts['category']); ?></span>
        </a>

        <div class="icons">
            <a href="../view_posts.php?post_id=<?= urlencode($post_id); ?>">
                <i class="fas fa-comment"></i><span>(<?= $total_post_comments; ?>)</span>
            </a>
            <button type="submit" name="like_post">
                <i class="fas fa-heart" style="<?= $user_liked ? 'color:var(--red);' : ''; ?>"></i>
                <span>(<?= $total_post_likes; ?>)</span>
            </button>
        </div>
    </form>
    <?php
        }
    } else {
        echo '<p class="empty">No posts found!</p>';
    }
    ?>
</div>

</body>
</html>
