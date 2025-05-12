<?php
session_start();

// Debug: Print session information
echo "Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "Not set") . "<br>";
echo "Session email: " . (isset($_SESSION['email']) ? $_SESSION['email'] : "Not set") . "<br>";
var_dump($_SESSION);

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bookswap";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $message = "Error: You must be logged in to add a book.";
} else {
    $user_id = $_SESSION['user_id'];
    echo "Debug - User ID from session: " . $user_id . "<br>";

    // Check if the user exists in the users table
    $user_check_sql = "SELECT id, email FROM users WHERE id = ?";
    $user_check_stmt = $conn->prepare($user_check_sql);
    $user_check_stmt->bind_param("i", $user_id);
    $user_check_stmt->execute();
    $user_check_result = $user_check_stmt->get_result();

    if ($user_check_result->num_rows == 0) {
        $message = "Error: Invalid user ID. Please log out and log in again.";
    } else {
        $user_data = $user_check_result->fetch_assoc();
        echo "Debug - User ID from database: " . $user_data['id'] . "<br>";
        echo "Debug - User email from database: " . $user_data['email'] . "<br>";

        // Check if form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Collect form data
            $title = $conn->real_escape_string($_POST['book-title']);
            $author = $conn->real_escape_string($_POST['book-author']);
            $condition = $conn->real_escape_string($_POST['book-condition']);
            $genre = $conn->real_escape_string($_POST['book-genre']);
            $details = $conn->real_escape_string($_POST['book-details']);
            $status = 'Available'; // Default status for new books

            echo "Debug - User ID being used for insertion: " . $user_id . "<br>";

            // Prepare SQL statement
            $sql = "INSERT INTO books (title, author, book_condition, genre, details, status, user_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            // Prepare and bind
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $title, $author, $condition, $genre, $details, $status, $user_id);

            // Execute SQL statement
            if ($stmt->execute()) {
                $_SESSION['message'] = "Book added successfully!";
                header("Location: my_books.php");
                exit();
            } else {
                $message = "Error: " . $stmt->error;
            }

            $stmt->close();
        }
    }
    $user_check_stmt->close();
}

// Close connection
$conn->close();

// Display the message
if (!empty($message)) {
    echo $message;
}

// Optionally, you can store the message in a session and display it on my_books.php
echo "<br><a href='exchange.php'>Back to Book Exchange</a>";
?>