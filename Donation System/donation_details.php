<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if donation ID is provided
if (!isset($_GET['id'])) {
    header("Location: donation.php");
    exit();
}

$donation_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'bookswap');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get donation details with user information
$sql = "SELECT d.*, u.name, u.email, u.city, u.zilla, u.phone 
        FROM donations d
        JOIN users u ON d.user_id = u.id
        WHERE d.id = ? AND d.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $donation_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: donation.php");
    exit();
}

$donation = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Details - BookSwap</title>
    <link rel="stylesheet" href="donation.css">
    <style>
        .donation-details-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .donation-details-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .donation-details-header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .donation-success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 5px;
            text-align: center;
            font-size: 18px;
        }
        
        .details-section {
            margin-bottom: 30px;
        }
        
        .details-section h2 {
            color: #3498db;
            border-bottom: 2px solid #eaeaea;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .details-row {
            display: flex;
            margin-bottom: 15px;
        }
        
        .details-label {
            font-weight: bold;
            width: 150px;
            color: #555;
        }
        
        .details-value {
            flex: 1;
            color: #333;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        
        .action-buttons a {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }
        
        .btn-primary:hover, .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="navbar-left">
                <ul class="nav-links left-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="donation_list.php">Donation Book List</a></li>
                </ul>
            </div>
            <div class="navbar-right">
                <ul class="nav-links right-links">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="profile.php">Profile</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="register.php">Register</a></li>
                        <li><a href="login.php">Sign in</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <main class="donation-details-container">
        <div class="donation-details-header">
            <h1>Donation Details</h1>
            <div class="donation-success">
                Thank you for your generous donation! Your contribution will help students in need.
            </div>
        </div>

        <div class="details-section">
            <h2>Book Information</h2>
            <div class="details-row">
                <div class="details-label">Title:</div>
                <div class="details-value"><?php echo htmlspecialchars($donation['title']); ?></div>
            </div>
            <div class="details-row">
                <div class="details-label">Author:</div>
                <div class="details-value"><?php echo htmlspecialchars($donation['author']); ?></div>
            </div>
            <div class="details-row">
                <div class="details-label">Book Type:</div>
                <div class="details-value"><?php echo htmlspecialchars(ucfirst($donation['book_type'])); ?></div>
            </div>
            <div class="details-row">
                <div class="details-label">Condition:</div>
                <div class="details-value"><?php echo htmlspecialchars(ucfirst($donation['book_condition'])); ?></div>
            </div>
            <div class="details-row">
                <div class="details-label">Description:</div>
                <div class="details-value"><?php echo htmlspecialchars($donation['description']); ?></div>
            </div>
            <div class="details-row">
                <div class="details-label">Donation Date:</div>
                <div class="details-value"><?php echo date('F j, Y, g:i a', strtotime($donation['donation_date'])); ?></div>
            </div>
        </div>

        <div class="details-section">
            <h2>Donor Information</h2>
            <div class="details-row">
                <div class="details-label">Name:</div>
                <div class="details-value"><?php echo htmlspecialchars($donation['name']); ?></div>
            </div>
            <div class="details-row">
                <div class="details-label">Email:</div>
                <div class="details-value"><?php echo htmlspecialchars($donation['email']); ?></div>
            </div>
            <div class="details-row">
                <div class="details-label">Phone:</div>
                <div class="details-value"><?php echo htmlspecialchars($donation['phone']); ?></div>
            </div>
            <div class="details-row">
                <div class="details-label">Location:</div>
                <div class="details-value"><?php echo htmlspecialchars($donation['city'] . ', ' . $donation['zilla']); ?></div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="donation.php" class="btn-primary">Donate Another Book</a>
            <a href="index.php" class="btn-secondary">Return to Home</a>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> BookSwap. All rights reserved.</p>
    </footer>
</body>
</html>