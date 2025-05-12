<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'bookswap');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's requests with additional information
$sql = "SELECT dr.id, d.title, d.author, u.name AS owner_name, u.city, u.zilla, u.email, dr.status, d.donation_date
        FROM donation_requests dr
        JOIN donations d ON dr.donation_id = d.id
        JOIN users u ON d.user_id = u.id
        WHERE dr.requester_id = ?
        ORDER BY d.donation_date DESC";

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
    <title>My Donation Requests - BookSwap</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #1e1e1e;
            padding: 20px 0;
        }
        nav ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
        }
        nav ul li {
            margin: 0 15px;
        }
        nav ul li a {
            color: #e0e0e0;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        nav ul li a:hover {
            color: #bb86fc;
        }
        h1 {
            text-align: center;
            color: #bb86fc;
            margin-bottom: 30px;
        }
        .request-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-top: 20px;
        }
        .request-table th, .request-table td {
            padding: 15px;
            text-align: left;
            background-color: #1e1e1e;
            transition: background-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
        }
        .request-table th {
            background-color: #2e2e2e;
            color: #bb86fc;
            font-weight: bold;
        }
        .request-table tr {
            transition: all 0.3s ease;
        }
        .request-table tr:hover td {
            background-color: #2e2e2e;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(187, 134, 252, 0.1);
        }
        .request-status {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 15px;
            display: inline-block;
        }
        .status-pending {
            background-color: #ffd700;
            color: #121212;
        }
        .status-accepted {
            background-color: #4caf50;
            color: #121212;
        }
        .status-rejected {
            background-color: #f44336;
            color: #121212;
        }
        footer {
            text-align: center;
            padding: 20px 0;
            background-color: #1e1e1e;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="donation_list.php">Donation Book List</a></li>
                <li><a href="donation_requests.php">My Donation Requests</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>My Donation Requests</h1>
        <?php if ($result->num_rows > 0): ?>
            <table class="request-table">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Owner</th>
                        <th>Location</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Requested On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['author']); ?></td>
                            <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['city'] . ', ' . $row['zilla']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <span class="request-status status-<?php echo strtolower($row['status']); ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('F j, Y', strtotime($row['donation_date'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #bb86fc;">You haven't made any donation requests yet.</p>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> BookSwap. All rights reserved.</p>
    </footer>

    <?php
    $stmt->close();
    $conn->close();
    ?>
</body>
</html>