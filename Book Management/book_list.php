<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'bookswap');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user has added any books (for condition 1)
$has_books = false;
if (isset($_SESSION['user_id'])) {
    $check_books_sql = "SELECT COUNT(*) as book_count FROM books WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_books_sql);
    $check_stmt->bind_param("i", $_SESSION['user_id']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $book_count = $check_result->fetch_assoc()['book_count'];
    $has_books = ($book_count > 0);
    $check_stmt->close();
}

$sql = "SELECT b.*, u.name AS owner_name 
        FROM books b
        JOIN users u ON b.user_id = u.id
        WHERE b.status = 'available'";

if (isset($_SESSION['user_id'])) {
    $sql .= " AND b.user_id != ?";
}

$stmt = $conn->prepare($sql);
if (isset($_SESSION['user_id'])) {
    $stmt->bind_param("i", $_SESSION['user_id']);
}
$stmt->execute();
$result = $stmt->get_result();

// Check which books the user has already requested (for condition 2)
$requested_books = [];
if (isset($_SESSION['user_id'])) {
    $request_check_sql = "SELECT book_id FROM book_requests WHERE requester_id = ?";
    $request_check_stmt = $conn->prepare($request_check_sql);
    $request_check_stmt->bind_param("i", $_SESSION['user_id']);
    $request_check_stmt->execute();
    $request_check_result = $request_check_stmt->get_result();
    
    while ($row = $request_check_result->fetch_assoc()) {
        $requested_books[] = $row['book_id'];
    }
    $request_check_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book List - BookSwap</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #1a1a1a;
            color: #ffffff;
        }

        header {
            background-color: #333;
            padding: 10px 0;
        }

        nav ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
        }

        nav ul li {
            margin: 0 10px;
        }

        nav ul li a {
            color: #fff;
            text-decoration: none;
        }

        main {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }

        h1 {
            text-align: center;
            color: #bb86fc;
        }

        .book-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .book-card {
            background-color: #2d2d2d;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .book-card h3 {
            color: #bb86fc;
            margin-top: 0;
        }

        .book-card p {
            margin: 5px 0;
        }

        .interested-btn, .interested-btn-link {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #bb86fc;
            color: #000;
            text-align: center;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .interested-btn:hover, .interested-btn-link:hover {
            background-color: #9966cc;
            transform: scale(1.05);
        }

        .disabled-btn {
            background-color: #666;
            color: #ccc;
            cursor: not-allowed;
        }

        .disabled-btn:hover {
            background-color: #666;
            transform: none;
        }

        .alert-message {
            background-color: #cf6679;
            color: #000;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }

        footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="book_list.php">Book List</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="my_books.php">My Books</a></li>
                    <li><a href="my_requests.php">My Requests</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="register.php">Register</a></li>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Available Books for Swap</h1>
        <?php if (isset($_SESSION['user_id'])): ?>
            <p style="text-align:center;">These are books available for swap from other users.</p>
            
            <?php if (!$has_books): ?>
                <div class="alert-message">
                    You need to add at least one book to your collection before you can request books from others.
                    <a href="exchange.php" style="color: #000; font-weight: bold;">Add a book now</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="book-list">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<div class='book-card'>";
                    echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
                    echo "<p><strong>Author:</strong> " . htmlspecialchars($row['author']) . "</p>";
                    echo "<p><strong>Condition:</strong> " . htmlspecialchars($row['book_condition']) . "</p>";
                    echo "<p><strong>Genre:</strong> " . htmlspecialchars($row['genre']) . "</p>";
                    echo "<p><strong>Owner:</strong> " . htmlspecialchars($row['owner_name']) . "</p>";

                    if (isset($_SESSION['user_id'])) {
                        // Check if user has already requested this book
                        if (in_array($row['id'], $requested_books)) {
                            echo "<button class='disabled-btn' disabled>Already Requested</button>";
                        } 
                        // Check if user has added any books
                        elseif (!$has_books) {
                            echo "<a href='exchange.php' class='interested-btn-link'>Add Books First</a>";
                        } 
                        // User can request this book
                        else {
                            echo "<form action='express_interest.php' method='post'>";
                            echo "<input type='hidden' name='book_id' value='" . $row['id'] . "'>";
                            echo "<input type='hidden' name='requester_id' value='" . $_SESSION['user_id'] . "'>";
                            echo "<button type='submit' class='interested-btn'>Interested</button>";
                            echo "</form>";
                        }
                    } else {
                        echo "<a href='login.php' class='interested-btn-link'>Interested</a>";
                    }

                    echo "</div>";
                }
            } else {
                echo "<p style='text-align:center;'>No books available for swap at the moment.</p>";
            }
            ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> BookSwap. All rights reserved.</p>
    </footer>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>