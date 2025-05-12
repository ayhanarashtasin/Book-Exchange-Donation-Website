<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or missing post ID']);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];

// Check if the user has already liked the post
$check_sql = "SELECT * FROM forum_likes WHERE user_id = ? AND post_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $user_id, $post_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // User has already liked the post, so remove the like
    $delete_sql = "DELETE FROM forum_likes WHERE user_id = ? AND post_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $user_id, $post_id);
    $delete_stmt->execute();
} else {
    // User hasn't liked the post, so add a new like
    $insert_sql = "INSERT INTO forum_likes (user_id, post_id) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ii", $user_id, $post_id);
    $insert_stmt->execute();
}

// Get the updated like count
$count_sql = "SELECT COUNT(*) as like_count FROM forum_likes WHERE post_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $post_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$like_count = $count_result->fetch_assoc()['like_count'];

echo json_encode(['success' => true, 'likes' => $like_count]);

$conn->close();