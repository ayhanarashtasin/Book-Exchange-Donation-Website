<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'bookswap');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Fetch all donated books except the current user's
$sql = "SELECT d.*, u.name AS donor_name, 
               (SELECT COUNT(*) FROM donation_requests WHERE donation_id = d.id) AS request_count
        FROM donations d
        JOIN users u ON d.user_id = u.id
        WHERE d.user_id != ?
        ORDER BY d.donation_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle book request
if (isset($_POST['request_book']) && isset($_SESSION['user_id'])) {
    $donation_id = $_POST['donation_id'];
    $requester_id = $_SESSION['user_id'];

    // Check if user has already requested this book
    $check_sql = "SELECT * FROM donation_requests WHERE donation_id = ? AND requester_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $donation_id, $requester_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows == 0) {
        // Insert new request
        $insert_sql = "INSERT INTO donation_requests (donation_id, requester_id) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ii", $donation_id, $requester_id);
        $insert_stmt->execute();
        $insert_stmt->close();

        // Refresh the page to update the request count
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    $check_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Book List - BookSwap</title>
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

        /* Header and Navigation */
        header {
            background-color: #1A1A1A;
            padding: 15px 0;
        }

        nav ul {
            list-style-type: none;
            display: flex;
            justify-content: center;
        }

        nav ul li {
            margin: 0 15px;
        }

        nav ul li a {
            color: #FFFFFF;
            text-decoration: none;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }

        nav ul li a:hover {
            color: #F39C12;
        }

        /* Main Container */
        main {
            padding: 30px;
            max-width: 1200px;
            margin: 30px auto;
            background-color: #1A1A1A;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.1);
        }

        h1 {
            font-size: 2rem;
            color: #F39C12;
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
            background-color: #2C2C2C;
            border: 1px solid #3A3A3A;
            border-radius: 10px;
            padding: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 8px rgba(255, 255, 255, 0.1);
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(255, 255, 255, 0.2);
        }

        .book-title {
            font-size: 1.6rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #F39C12;
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
            color: #16A085;
        }

        .request-btn {
            background-color: #1ABC9C;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            text-align: center;
            margin-top: 15px;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
        }

        .request-btn:hover {
            background-color: #16A085;
        }

        .request-btn:disabled {
            background-color: #555555;
            cursor: not-allowed;
        }

        .login-link {
            display: block;
            margin-top: 20px;
            font-size: 1rem;
            color: #F39C12;
            text-decoration: none;
            text-align: center;
        }

        .login-link:hover {
            text-decoration: underline;
        }

        /* Footer */
        footer {
            background-color: #1A1A1A;
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
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
<header>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="donation_list.php">Donation Book List</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="donation_requests.php">My Requests</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

    <main>
        <h1>Donated Books Available</h1>
        <div class="book-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="book-card">
                        <div class="book-title"><?php echo htmlspecialchars($row['title']); ?></div>
                        <div class="book-details">Author: <?php echo htmlspecialchars($row['author']); ?></div>
                        <div class="book-details">Type: <?php echo htmlspecialchars($row['book_type']); ?></div>
                        <div class="book-details">Donor: <?php echo htmlspecialchars($row['donor_name']); ?></div>
                        <div class="book-details">
                            Requests: 
                            <span class="request-count"><?php echo $row['request_count']; ?></span>
                        </div>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php
                            $user_requested = false;
                            $check_user_request = "SELECT * FROM donation_requests WHERE donation_id = ? AND requester_id = ?";
                            $check_stmt = $conn->prepare($check_user_request);
                            $check_stmt->bind_param("ii", $row['id'], $_SESSION['user_id']);
                            $check_stmt->execute();
                            $user_requested = $check_stmt->get_result()->num_rows > 0;
                            $check_stmt->close();
                            ?>
                            <form method="POST">
                                <input type="hidden" name="donation_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="request_book" class="request-btn" <?php echo $user_requested ? 'disabled' : ''; ?>>
                                    <?php echo $user_requested ? 'Requested' : 'Request Book'; ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="login.php" class="login-link">Login to Request</a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No donated books available at the moment.</p>
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