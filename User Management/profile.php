<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bookswap";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - BookSwap</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #1e1e1e;
            padding: 1rem 0;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .nav-links {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
        }

        .nav-links li {
            margin-right: 1rem;
        }

        .nav-links a {
            color: #bb86fc;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #3700b3;
        }

        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #1e1e1e;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #bb86fc;
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-info p {
            margin: 0.5rem 0;
        }

        .profile-actions {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #bb86fc;
            color: #000;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #3700b3;
            color: #fff;
        }

        footer {
            background-color: #1e1e1e;
            color: #e0e0e0;
            text-align: center;
            padding: 1rem 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="navbar-left">
                <ul class="nav-links left-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#">Explore</a></li>
                    <li><a href="#">Book List</a></li>
                </ul>
            </div>
            <div class="navbar-right">
                <ul class="nav-links right-links">
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="profile-container">
        <h1>User Profile</h1>
        <div class="profile-info">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Zilla (District):</strong> <?php echo htmlspecialchars($user['zilla']); ?></p>
            <p><strong>City:</strong> <?php echo htmlspecialchars($user['city']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
        </div>
        <div class="profile-actions">
            <a href="edit_profile.php" class="btn">Edit Profile</a>
            <a href="change_password.php" class="btn">Change Password</a>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> BookSwap. All rights reserved.</p>
    </footer>
</body>
</html>