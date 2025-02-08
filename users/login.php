<?php
//login.php
include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
};

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);
    $pass = sha1($_POST['pass']);
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);

    $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ?");
    $select_user->execute([$email, $pass]);
    $row = $select_user->fetch(PDO::FETCH_ASSOC);

    if ($select_user->rowCount() > 0) {
        $_SESSION['user_id'] = $row['id'];
        header('location:home.php');
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
    <title>Login</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .community-header {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 20px;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .form-container h3 {
            margin-bottom: 20px;
            color: #333;
        }

        .form-container .box {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-container .box:focus {
            border-color: #007bff;
            outline: none;
        }

        .form-container .btn {
            background: #007bff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background 0.3s;
        }

        .form-container .btn:hover {
            background: #0056b3;
        }

        .message {
            margin: 15px 0;
            color: red;
            font-weight: bold;
        }

        p {
            margin-top: 15px;
        }

        a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <!-- Header section starts -->
    <?php include 'components/user_header.php'; ?>
    <!-- Header section ends -->

    

    <section class="form-container">
        <form action="" method="post">
            <h3>Login Now</h3>
            <?php if (isset($message)) { foreach ($message as $msg) { echo '<div class="message">'.$msg.'</div>'; } } ?>
            <input type="email" name="email" required placeholder="Enter your email" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="password" name="pass" required placeholder="Enter your password" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="submit" value="Login Now" name="submit" class="btn">
            <p>Don't have an account? <a href="register.php">Register Now</a></p>
        </form>
    </section>

    <!-- Footer section -->
    <?php include 'components/footer.php'; ?>

    <!-- Custom JS file link -->
    <script src="js/script.js"></script>

</body>

</html>
