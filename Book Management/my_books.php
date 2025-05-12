<?php
session_start();

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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Please log in to view your books.");
}

$user_id = $_SESSION['user_id'];

// Prepare SQL statement to fetch user's books with request count
$sql = "SELECT b.*, COUNT(r.id) as request_count 
        FROM books b 
        LEFT JOIN book_requests r ON b.id = r.book_id 
        WHERE b.user_id = ? 
        GROUP BY b.id";
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
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #e4f7fb;
            margin: 0;
            padding: 0;
            color: #333;
        }
        header {
            background-color: #2d3e50;
            color: #fff;
            padding: 15px 0;
            text-align: center;
        }
        header h1 {
            margin: 0;
            font-size: 2.5em;
        }
        nav ul {
            list-style: none;
            padding: 0;
        }
        nav ul li {
            display: inline;
            margin: 0 10px;
        }
        nav ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 1.1em;
            transition: color 0.3s ease;
        }
        nav ul li a:hover {
            color: #ff7f50;
        }
        .book-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
        }
        .book-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            margin: 20px;
            padding: 25px;
            width: 280px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeIn 0.5s ease-in-out;
        }
        .book-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .book-title {
            color: #333;
            font-size: 1.3em;
            font-weight: bold;
            margin-bottom: 12px;
        }
        .book-author {
            color: #5f6368;
            font-style: italic;
            margin-bottom: 12px;
        }
        .book-details {
            color: #444;
            font-size: 1em;
            margin-bottom: 8px;
        }
        .book-status {
            background-color: #ff7f50;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            display: inline-block;
            margin-top: 15px;
            transition: background-color 0.3s ease;
        }
        .book-status:hover {
            background-color: #ff6347;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        footer {
            background-color: #2d3e50;
            color: #fff;
            padding: 10px 0;
            text-align: center;
        }
        .request-count {
            font-size: 0.8em;
            color: #4CAF50;
            margin-left: 10px;
        }
        .view-requests-btn {
            display: inline-block;
            margin-top: 12px;
            background: #2563eb;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .view-requests-btn:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>
<body>
    <header>
        <h1>My Books</h1>
        <nav>
            <ul>
                <li><a href="exchange.php">Back to Book Exchange</a></li>
                <li><a href="index.php">Home</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2 style="text-align:center; font-size: 2em; margin-top: 20px;">Your Books</h2>
        <?php if ($result->num_rows > 0): ?>
            <div class="book-container">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="book-card">
                        <div class="book-title">
                            <?php echo htmlspecialchars($row['title']); ?>
                            <span class="request-count">(<?php echo $row['request_count']; ?> requests)</span>
                        </div>
                        <div class="book-author">by <?php echo htmlspecialchars($row['author']); ?></div>
                        <div class="book-details">Condition: <?php echo htmlspecialchars($row['book_condition']); ?></div>
                        <div class="book-details">Genre: <?php echo htmlspecialchars($row['genre']); ?></div>
                        <?php if (!empty($row['details'])): ?>
                            <div class="book-details">Details: <?php echo htmlspecialchars($row['details']); ?></div>
                        <?php endif; ?>
                        <div class="book-status"><?php echo htmlspecialchars($row['status']); ?></div>

                        <!-- View Requests Button -->
                        <a href="book_requests.php?book_id=<?php echo $row['id']; ?>" class="view-requests-btn">
                            View Requests
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center;">You haven't added any books yet.</p>
        <?php endif; ?>
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