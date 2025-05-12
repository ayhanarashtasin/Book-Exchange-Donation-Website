<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bookswap";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT b.*, 
               (SELECT COUNT(DISTINCT requester_id) 
                FROM book_requests 
                WHERE book_id = b.id) AS request_count 
        FROM books b 
        WHERE b.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Books - BookSwap</title>
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

        .book-card a {
            display: inline-block;
            padding: 8px 12px;
            background-color: #bb86fc;
            color: #000;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
            margin-right: 5px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .book-card a:hover {
            background-color: #9966cc;
            transform: scale(1.05);
        }

        .book-card a.requests {
            background-color: #03dac6;
        }

        .book-card a.requests:hover {
            background-color: #018786;
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
                <li><a href="book_list.php">All Books</a></li>
                <li><a href="add_book.php">Add New Book</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>My Books</h1>
        <div class="book-list">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<div class='book-card'>";
                    echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
                    echo "<p><strong>Author:</strong> " . htmlspecialchars($row['author']) . "</p>";
                    echo "<p><strong>Condition:</strong> " . htmlspecialchars($row['book_condition']) . "</p>";
                    echo "<p><strong>Genre:</strong> " . htmlspecialchars($row['genre']) . "</p>";

                    echo "<a href='book_requests.php?book_id=" . $row['id'] . "' class='requests'>Requests (" . $row['request_count'] . ")</a>";
                    echo "<a href='book_details.php?id=" . $row['id'] . "'>View Details</a>";
                    echo "<a href='edit_book.php?id=" . $row['id'] . "'>Edit</a>";
                    echo "<a href='delete_book.php?id=" . $row['id'] . "' onclick='return confirm(\"Are you sure you want to delete this book?\");'>Delete</a>";
                    echo "</div>";
                }
            } else {
                echo "<p style='text-align: center; color: #bb86fc;'>You haven't added any books yet.</p>";
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