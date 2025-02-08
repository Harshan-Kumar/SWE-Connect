<?php
// reference_books.php
include '../components/connect.php';

session_start();

// Handle book deletion request
if (isset($_POST['delete_book'])) {
    $book_id = $_POST['book_id'];
    $book_id = filter_var($book_id, FILTER_SANITIZE_STRING);

    // Prepare and execute the delete query
    $delete_book = $conn->prepare("DELETE FROM `reference_books` WHERE id = ?");
    $delete_book->execute([$book_id]);

    if ($delete_book) {
        $message[] = 'Book deleted successfully!';
    } else {
        $message[] = 'Failed to delete book!';
    }
}

// Fetch reference books with optional course code filter
$course_code_filter = isset($_GET['course_code']) ? htmlspecialchars($_GET['course_code']) : '';
if ($course_code_filter) {
    $select_books = $conn->prepare("SELECT * FROM `reference_books` WHERE course_code = ? ORDER BY upload_date DESC");
    $select_books->execute([$course_code_filter]);
} else {
    $select_books = $conn->prepare("SELECT * FROM `reference_books` ORDER BY upload_date DESC");
    $select_books->execute();
}

// Fetch available course codes for filtering
$course_codes = [
    'EEE1019', 'MAT1016', 'MAT2002', 'SWE1003', 'SWE1004', 'SWE1005',
    'SWE1006', 'SWE1007', 'SWE1701', 'SWE2001', 'SWE2002', 'SWE2003',
    'SWE2004', 'SWE2005', 'SWE2006', 'SWE2007', 'SWE3001', 'SWE3002',
    'SWE3004', 'BIT1029', 'CSE3501', 'CSE3502', 'MAT3001', 'MAT3002',
    'SWE1002', 'SWE1008', 'SWE1009', 'SWE1010', 'SWE1011', 'SWE1012',
    'SWE1013', 'SWE1014', 'SWEIOIS', 'SWE1017', 'SWE1018', 'SWE2008', 'SWE2027'
    // Add more course codes as needed
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Manage Reference Books</title>
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

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
        <?php if ($select_books->rowCount() > 0): ?>
            <?php while ($book = $select_books->fetch(PDO::FETCH_ASSOC)): ?>
                <?php
                // Check if the uploaded_by starts with 'User-' to identify user uploads
                if (strpos($book['uploaded_by'], 'User-') === 0) {
                    // Extract user ID from the uploaded_by field
                    $user_id = str_replace('User-', '', $book['uploaded_by']);
                    // Fetch the user's name from the database
                    $get_user = $conn->prepare("SELECT name FROM `users` WHERE id = ?");
                    $get_user->execute([$user_id]);
                    $user_name = $get_user->fetch(PDO::FETCH_ASSOC)['name'] ?? 'Unknown User';
                } else {
                    // If it's an admin upload, display admin name
                    $user_name = $book['uploaded_by']; // Assuming this is an admin's name
                }
                ?>
                <div class="box">
                    <h3><?= htmlspecialchars($book['title']); ?></h3>
                    <p><?= htmlspecialchars($book['description']); ?></p>
                    <p><strong>Course Code:</strong> <?= htmlspecialchars($book['course_code']); ?></p>
                    <p><strong>Uploaded By:</strong> <?= htmlspecialchars($user_name); ?></p>
                    <a href="../uploaded_books/<?= htmlspecialchars($book['file_path']); ?>" target="_blank" class="btn">View/Download</a>
                    <form action="" method="POST">
                        <input type="hidden" name="book_id" value="<?= $book['id']; ?>">
                        <button type="submit" name="delete_book" class="delete-btn" onclick="return confirm('Are you sure you want to delete this book?');">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty">No reference books uploaded yet!</p>
        <?php endif; ?>
    </div>
</section>

<!-- Custom JS file link -->
<script src="../js/admin_script.js"></script>
</body>
</html>
