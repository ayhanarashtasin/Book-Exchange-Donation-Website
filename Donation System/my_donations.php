<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'bookswap');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Check if donation_requests table exists
$table_check = $conn->query("SHOW TABLES LIKE 'donation_requests'");
if($table_check->num_rows == 0) {
    // If table doesn't exist, use a query without the subquery
    $sql = "SELECT d.* FROM donations d WHERE d.user_id = ?";
} else {
    // If table exists, use the original query
    $sql = "SELECT d.*, 
                   (SELECT COUNT(*) FROM donation_requests WHERE donation_id = d.id) AS request_count 
            FROM donations d 
            WHERE d.user_id = ?";
}

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
    <title>My Donated Books - BookSwap</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* General Reset */
        body, h1, h2, p, ul, li, a, button, input, form {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        /* Body */
        body {
            background-color: #000000;
            color: #FFFFFF;
        }

        /* Main Container */
        main {
            padding: 40px;
            max-width: 1200px;
            margin: 30px auto;
            background-color: #111111;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.1);
        }

        h1 {
            font-size: 2.4rem;
            color: #bb86fc;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Grid Layout for Books */
        .book-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .book-card {
            background-color: #1A1A1A;
            border: 1px solid #333333;
            border-radius: 10px;
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 6px 18px rgba(255, 255, 255, 0.05);
        }

        .book-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 24px rgba(255, 255, 255, 0.1);
        }

        .book-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 12px;
            color: #bb86fc;
            text-transform: uppercase;
        }

        .book-details {
            font-size: 1rem;
            margin-bottom: 8px;
            color: #CCCCCC;
        }

        .book-details strong {
            color: #FFFFFF;
        }

        .request-count {
            font-weight: bold;
            color: #00FF00;
        }

        .view-requests-btn {
            background-color: #1A1A1A;
            color: #bb86fc;
            padding: 10px 20px;
            border-radius: 6px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
            transition: background-color 0.3s ease, color 0.3s ease;
            font-size: 1rem;
            border: 1px solid #bb86fc;
        }

        .view-requests-btn:hover {
            background-color: #bb86fc;
            color: #000000;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            font-size: 1.1rem;
            color: #bb86fc;
            text-decoration: none;
            text-align: center;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* Footer */
        footer {
            background-color: #111111;
            color: #FFFFFF;
            text-align: center;
            padding: 15px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        footer p {
            margin: 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .book-card {
                padding: 15px;
            }

            h1 {
                font-size: 1.8rem;
            }

            .view-requests-btn {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>

    <main>
        <h1>My Donated Books</h1>
        <div class="book-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="book-card">
                        <div class="book-title"><?php echo htmlspecialchars($row['title']); ?></div>
                        <div class="book-details">Author: <?php echo htmlspecialchars($row['author']); ?></div>
                        <div class="book-details">Type: <?php echo htmlspecialchars($row['book_type']); ?></div>
                        <div class="book-details">Status: <?php echo htmlspecialchars($row['status']); ?></div>
                        <div class="book-details">
                            Requests: 
                            <span class="request-count">
                                <?php 
                                if(isset($row['request_count'])) {
                                    echo $row['request_count'];
                                } else {
                                    echo "N/A";
                                }
                                ?>
                            </span>
                        </div>
                        <a href="view_requests.php?donation_id=<?php echo $row['id']; ?>" class="view-requests-btn">View Requests</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #CCCCCC;">You haven't donated any books yet.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> BookSwap. All rights reserved.</p>
    </footer>

    <?php
    $stmt->close();
    $conn->close();
    ?>

</body>
</html>
