<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'bookswap');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $book_id = $_POST['book_id'];
    $requester_id = $_SESSION['user_id']; // Always trust session, not POST for auth
    $request_date = date('Y-m-d H:i:s');
    $status = 'pending';

    // Check if this user already requested this book
    $check_sql = "SELECT * FROM book_requests WHERE book_id = ? AND requester_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $book_id, $requester_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows == 0) {
        // Insert new book request
        $insert_sql = "INSERT INTO book_requests (book_id, requester_id, request_date, status, requests) 
                       VALUES (?, ?, ?, ?, 1)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iiss", $book_id, $requester_id, $request_date, $status);

        if ($insert_stmt->execute()) {
            $_SESSION['message'] = "Your interest has been recorded successfully!";
        } else {
            $_SESSION['error'] = "Error recording your interest. Please try again.";
        }

        $insert_stmt->close();
    } else {
        $_SESSION['error'] = "You have already expressed interest in this book.";
    }

    $check_stmt->close();
} else {
    $_SESSION['error'] = "Invalid request or user not logged in.";
}

$conn->close();
header("Location: book_list.php");
exit();
?>
