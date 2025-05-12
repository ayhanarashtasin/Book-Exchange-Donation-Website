<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'bookswap');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to view your requests.";
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT 
        b.title,
        u.name AS owner_name,
        u.city,
        u.zilla,
        u.email AS owner_email,  /* Added email column */
        r.status,
        r.request_date,
        r.id AS request_id,
        b.id AS book_id,
        b.status AS book_status
    FROM book_requests r
    JOIN books b ON r.book_id = b.id
    JOIN users u ON b.user_id = u.id
    WHERE r.requester_id = ?
    ORDER BY r.request_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<?php
// ... (PHP code remains unchanged)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Book Requests</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            margin: 0;
            padding: 0;
        }
        h1 {
            text-align: center;
            margin: 2rem 0;
            color: #bb86fc;
            font-size: 32px;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #2a2a2a;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #3a3a3a;
        }
        th {
            background-color: #bb86fc;
            color: #121212;
            font-weight: bold;
        }
        tr:hover {
            background-color: #333333;
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }
        a {
            color: #03dac6;
            text-decoration: none;
        }
        a:hover {
            color: #018786;
        }
        .status {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
        }
        .status.pending {
            background-color: #ffd700;
            color: #121212;
        }
        .status.accepted {
            background-color: #03dac6;
            color: #121212;
        }
        .status.rejected {
            background-color: #cf6679;
            color: #121212;
        }
        footer {
            text-align: center;
            padding: 20px;
            background-color: #1e1e1e;
            color: #e0e0e0;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Books I've Requested</h1>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Book Title</th>
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
                        <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['zilla'] . ', ' . $row['city']); ?></td>
                        <td><a href="mailto:<?php echo htmlspecialchars($row['owner_email']); ?>"><?php echo htmlspecialchars($row['owner_email']); ?></a></td>
                        <td>
                            <span class="status <?php echo strtolower($row['status']); ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y, H:i', strtotime($row['request_date'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center; color: #bb86fc;">You haven't requested any books yet.</p>
    <?php endif; ?>

</div>

<footer>
    <p>&copy; <?php echo date("Y"); ?> BookSwap. All rights reserved.</p>
</footer>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

<?php
$stmt->close();
$conn->close();
?>
