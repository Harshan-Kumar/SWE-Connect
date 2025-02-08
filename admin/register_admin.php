<?php
// register_admin.php
include '../components/connect.php';

session_start();

// Check if the session ID is set, if not redirect to login

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);

    $pass = $_POST['pass'];
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);

    $cpass = $_POST['cpass'];
    $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

    if ($pass != $cpass) {
        $message[] = 'Confirm password does not match!';
    } else {
        try {
            $select_admin = $conn->prepare("SELECT * FROM `admin` WHERE name = ?");
            $select_admin->execute([$name]);

            if ($select_admin->rowCount() > 0) {
                $message[] = 'Username already exists!';
            } else {
                // Use password_hash() for secure password storage
                $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
                $insert_admin = $conn->prepare("INSERT INTO `admin` (name, password) VALUES (?, ?)");
                $insert_admin->execute([$name, $hashed_pass]);

                if ($insert_admin->rowCount() > 0) {
                    $message[] = 'New admin registered successfully!';
                    header('Location: admin_login.php');  // Redirect after success
                    exit();
                }
            }
        } catch (PDOException $e) {
            $message[] = 'Error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register Admin</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<!-- Register admin section starts -->
<section class="form-container">
   <form action="" method="POST">
      <h3>Register New Admin</h3>
      <input type="text" name="name" maxlength="20" required placeholder="Enter your username" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="pass" maxlength="20" required placeholder="Enter your password" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="cpass" maxlength="20" required placeholder="Confirm your password" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="Register Now" name="submit" class="btn">
   </form>
</section>
<!-- Register admin section ends -->

<?php
if (isset($message)) {
    foreach ($message as $msg) {
        echo '<p class="message">' . htmlspecialchars($msg) . '</p>';
    }
}
?>

<!-- Custom JS file link -->
<script src="../js/admin_script.js"></script>

</body>
</html>
