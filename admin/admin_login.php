<?php
// admin_login.php
include '../components/connect.php';

session_start();

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $pass = sha1($_POST['pass']);
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);

    // Check if the credentials match the admin table
    $select_admin = $conn->prepare("SELECT * FROM `admin` WHERE name = ? AND password = ?");
    $select_admin->execute([$name, $pass]);

    if ($select_admin->rowCount() > 0) {
        $fetch_admin = $select_admin->fetch(PDO::FETCH_ASSOC);
        $_SESSION['admin_id'] = $fetch_admin['id']; // Store the admin session
        header('location: dashboard.php'); // Redirect to the admin dashboard
    } else {
        $message[] = 'Incorrect username or password!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<section class="form-container">
    <form action="" method="POST">
        <h3>Admin Login</h3>
        <input type="text" name="name" required placeholder="Enter your username" class="box">
        <input type="password" name="pass" required placeholder="Enter your password" class="box">
        <input type="submit" value="Login" name="submit" class="btn">
    </form>

    <p>Don't have an admin account? <a href="register_admin.php" class="register-btn">Register as New Admin</a></p>
</section>

<!-- Custom JS file link -->
<script src="../js/admin_script.js"></script>

</body>
</html>
