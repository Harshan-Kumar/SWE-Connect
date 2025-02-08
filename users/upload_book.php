<?php
// upload_book.php
include 'components/connect.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);
    $uploaded_by = 'User-' . $_SESSION['user_id'];
    $course_code = htmlspecialchars($_POST['course_code']); // Get course code

    if (!empty($_FILES['book_file']['name'])) {
        $file_name = time() . '_' . $_FILES['book_file']['name'];
        $file_tmp = $_FILES['book_file']['tmp_name'];
        $file_path = '' . $file_name;

        // Ensure uploaded_books directory exists
        if (!is_dir('uploaded_books')) {
            mkdir('uploaded_books', 0777, true);
        }

        // Move uploaded file
        if (move_uploaded_file($file_tmp, $file_path)) {
            // Insert book details into database including course code
            $insert_book = $conn->prepare("INSERT INTO `reference_books` (title, description, file_path, uploaded_by, course_code) VALUES (?, ?, ?, ?, ?)");
            $insert_book->execute([$title, $description, $file_path, $uploaded_by, $course_code]);

            header('Location: upload_book.php?success=book_uploaded');
            exit;
        } else {
            $error_message = 'Failed to upload file. Please try again.';
        }
    } else {
        $error_message = 'Please select a file to upload.';
    }
}

// Fetch reference books
$course_code_filter = isset($_GET['course_code']) ? htmlspecialchars($_GET['course_code']) : '';
if ($course_code_filter) {
    $fetch_books = $conn->prepare("SELECT * FROM `reference_books` WHERE course_code = ? ORDER BY upload_date DESC");
    $fetch_books->execute([$course_code_filter]);
} else {
    $fetch_books = $conn->prepare("SELECT * FROM `reference_books` ORDER BY upload_date DESC");
    $fetch_books->execute();
}

// Predefined list of course codes
$course_codes = [
    'EEE1019', 'MAT1016', 'MAT2002', 'SWE1003', 'SWE1004', 'SWE1005', 
    'SWE1006', 'SWE1007', 'SWE1701', 'SWE2001', 'SWE2002', 'SWE2003', 
    'SWE2004', 'SWE2005', 'SWE2006', 'SWE2007', 'SWE3001', 'SWE3002', 
    'SWE3004', 'BIT1029', 'CSE3501', 'CSE3502', 'MAT3001', 'MAT3002', 
    'SWE1002', 'SWE1008', 'SWE1009', 'SWE1010', 'SWE1011', 'SWE1012', 
    'SWE1013', 'SWE1014', 'SWE1O15', 'SWE1017', 'SWE1018', 'SWE2008', 'SWE2027',
    // Add more course codes as needed
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload & View Reference Books</title>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css"> <!-- Include your existing styles -->
</head>
<body>

<?php include 'components/user_header.php'; // Include the header ?>

<section class="form-container">
    <form action="" method="POST" enctype="multipart/form-data">
        <h3>Upload Reference Book</h3>

        <?php if (isset($error_message)): ?>
            <p class="error-message" style="color: red;"><?= $error_message; ?></p>
        <?php elseif (isset($_GET['success'])): ?>
            <p class="success-message" style="color: green;">Book uploaded successfully!</p>
        <?php endif; ?>

        <label for="title">Book Title</label>
        <input type="text" name="title" id="title" class="box" required placeholder="Enter book title">

        <label for="description">Description</label>
        <textarea name="description" id="description" class="box" required placeholder="Write a short description"></textarea>

        <label for="book_file">Upload Book File</label>
        <input type="file" name="book_file" id="book_file" class="box" required accept=".pdf,.doc,.docx,.txt">

        <label for="course_code">Course Code</label>
        <select name="course_code" id="course_code" class="box" required>
            <option value="">Select Course Code</option>
            <?php foreach ($course_codes as $course_code): ?>
                <option value="<?= $course_code; ?>"><?= $course_code; ?></option>
            <?php endforeach; ?>
        </select>

        <input type="submit" value="Upload Book" class="btn">
    </form>
</section>

<section class="filter-section">
    <form action="" method="GET">
        <label for="course_code">Filter by Course Code</label>
        <select name="course_code" id="course_code">
            <option value="">All Courses</option>
            <?php foreach ($course_codes as $course_code): ?>
                <option value="<?= $course_code; ?>" <?= ($course_code_filter == $course_code) ? 'selected' : ''; ?>><?= $course_code; ?></option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Filter" class="btn">
    </form>
</section>

<section class="reference-books-container">
    <h1 class="heading">Uploaded Reference Books</h1>
    <div class="box-container">
        <?php if ($fetch_books->rowCount() > 0): ?>
            <?php while ($book = $fetch_books->fetch(PDO::FETCH_ASSOC)): ?>
                <?php
                // Check if the uploaded_by starts with 'User-' to identify user uploads
                if (strpos($book['uploaded_by'], 'User-') === 0) {
                    $user_id = str_replace('User-', '', $book['uploaded_by']);
                    $get_user = $conn->prepare("SELECT name FROM `users` WHERE id = ?");
                    $get_user->execute([$user_id]);
                    $user_name = $get_user->fetch(PDO::FETCH_ASSOC)['name'] ?? 'Unknown User';
                } else {
                    $user_name = $book['uploaded_by']; // Assume it's an admin name
                }
                ?>
                <div class="box">
                    <h3><?= htmlspecialchars($book['title']); ?></h3>
                    <p><?= htmlspecialchars($book['description']); ?></p>
                    <p><strong>Course Code:</strong> <?= htmlspecialchars($book['course_code']); ?></p>
                    <p><strong>Uploaded By:</strong> <?= htmlspecialchars($user_name); ?></p>
                    <a href="<?= htmlspecialchars($book['file_path']); ?>" target="_blank" class="btn">View/Download</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty">No reference books uploaded yet!</p>
        <?php endif; ?>
    </div>
</section>

<?php include 'components/footer.php'; // Include the footer ?>

<script src="js/script.js"></script>
</body>
</html>
