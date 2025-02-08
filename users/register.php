<?php
//register.php
include 'components/connect.php';

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $reg_no = $_POST['reg_no'];
    $reg_no = filter_var($reg_no, FILTER_SANITIZE_STRING);
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    $pass = sha1($_POST['pass']);
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);
    $cpass = sha1($_POST['cpass']);
    $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

    // Check if it's a valid VIT email
    if (strpos($email, '@vitstudent.ac.in') === false) {
        $message[] = 'Invalid VIT email address!';
    } elseif (!preg_match('/^(1[7-9]|2[0-4])MIS0(0[1-9][0-9]?|[1-7][0-9]{2}|800)$/', $reg_no)) {
        // Validate the registration number format
        $message[] = 'Enter a valid registration number (e.g., 22MIS0463)';
    } else {
        // Check if email or reg no already exists
        $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? OR reg_no = ?");
        $select_user->execute([$email, $reg_no]);

        if ($select_user->rowCount() > 0) {
            $message[] = 'Email or registration number already exists!';
        } else {
            if ($pass != $cpass) {
                $message[] = 'Passwords do not match!';
            } else {
                $insert_user = $conn->prepare("INSERT INTO `users`(name, reg_no, email, password) VALUES(?, ?, ?, ?)");
                $insert_user->execute([$name, $reg_no, $email, $pass]);
                $message[] = 'Registration successful!';
                
                // Fetch the user data after insertion
                $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
                $select_user->execute([$email]);
                $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);
                
                $_SESSION['user_id'] = $fetch_user['id'];
                header('location:login.php');
                exit(); // Ensure the script stops after redirecting
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Header section starts -->
<?php include 'components/user_header.php'; ?>
<!-- Header section ends -->

<section class="form-container">
    <form action="" method="POST">
        <h3>Register Now</h3>
        <?php
        // Display messages
        if (isset($message)) {
            foreach ($message as $msg) {
                echo '<div class="message">' . htmlspecialchars($msg) . '</div>';
            }
        }
        ?>
        <input type="text" name="name" required placeholder="Enter your full name" class="box">
        <input type="text" name="reg_no" required placeholder="Enter your registration number" class="box">
        <input type="email" name="email" required placeholder="Enter your VIT email" class="box">
        <input type="password" name="pass" required placeholder="Enter your password" class="box">
        <input type="password" name="cpass" required placeholder="Confirm your password" class="box">
        <input type="submit" name="submit" value="Register Now" class="btn">
    </form>
</section>

<!-- Footer section -->
<?php include 'components/footer.php'; ?>

<!-- Custom JS file link -->
<script src="js/script.js"></script>

</body>
</html>
