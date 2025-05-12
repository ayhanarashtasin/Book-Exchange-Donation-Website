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

$donation_id = isset($_GET['donation_id']) ? intval($_GET['donation_id']) : 0;

// Handle accept/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($request_id && ($action === 'accept' || $action === 'reject')) {
        // Update the request status
        $new_status = ($action === 'accept') ? 'accepted' : 'rejected';
        $update_sql = "UPDATE donation_requests SET status = ? WHERE id = ? AND donation_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sii", $new_status, $request_id, $donation_id);
        $update_stmt->execute();
        $update_stmt->close();

        if ($action === 'accept') {
            // Update the donation status to 'donated' in your donations table
            // Assuming you have a 'status' column in your donations table
            $donate_sql = "UPDATE donations SET status = 'donated' WHERE id = ?";
            $donate_stmt = $conn->prepare($donate_sql);
            $donate_stmt->bind_param("i", $donation_id);
            $donate_stmt->execute();
            $donate_stmt->close();
        }

        // Redirect to refresh the page
        header("Location: view_requests.php?donation_id=" . $donation_id);
        exit();
    }
}

// Fetch requests for the specific donation with user details
$sql = "SELECT dr.*, u.name as requester_name, u.email, u.zilla, u.city
        FROM donation_requests dr 
        JOIN users u ON dr.requester_id = u.id 
        WHERE dr.donation_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donation_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch donation details
$donation_sql = "SELECT * FROM donations WHERE id = ?";
$donation_stmt = $conn->prepare($donation_sql);
$donation_stmt->bind_param("i", $donation_id);
$donation_stmt->execute();
$donation_result = $donation_stmt->get_result();
$donation = $donation_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Requests - BookSwap</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #121212;
            color: #e0e0e0;
        }
        header {
            background-color: #1e1e1e;
            color: #ffffff;
            text-align: center;
            padding: 1rem 0;
        }
        main {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        h1 {
            color: #bb86fc;
        }
        .request-list {
            list-style-type: none;
            padding: 0;
        }
        .request-item {
            background-color: #1e1e1e;
            border-radius: 8px;
            margin-bottom: 1rem;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .request-details {
            margin-top: 0.5rem;
        }
        .action-buttons {
            margin-top: 1rem;
        }
        .action-button {
            padding: 0.5rem 1rem;
            margin-right: 0.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .accept-button {
            background-color: #03dac6;
            color: #000000;
        }
        .accept-button:hover {
            background-color: #018786;
        }
        .reject-button {
            background-color: #cf6679;
            color: #000000;
        }
        .reject-button:hover {
            background-color: #b00020;
        }
        .status-accepted {
            color: #03dac6;
            font-weight: bold;
        }
        .status-rejected {
            color: #cf6679;
            font-weight: bold;
        }
        .back-link {
            display: inline-block;
            margin-top: 1rem;
            color: #bb86fc;
            text-decoration: none;
            padding: 0.5rem 1rem;
            background-color: #1e1e1e;
            border-radius: 4px;
        }
        .back-link:hover {
            background-color: #2e2e2e;
        }
        footer {
            background-color: #1e1e1e;
            color: #ffffff;
            text-align: center;
            padding: 1rem 0;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <header>
        <h1>BookSwap</h1>
    </header>

    <main>
        <h1>Requests for "<?php echo htmlspecialchars($donation['title']); ?>"</h1>
        <?php if ($result->num_rows > 0): ?>
            <ul class="request-list">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li class="request-item">
                        <h3><?php echo htmlspecialchars($row['requester_name']); ?></h3>
                        <div class="request-details">
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                            <p><strong>Zilla:</strong> <?php echo htmlspecialchars($row['zilla']); ?></p>
                            <p><strong>City:</strong> <?php echo htmlspecialchars($row['city']); ?></p>
                            <p><strong>Request Time:</strong> <?php echo date('Y-m-d H:i:s', strtotime($row['request_date'])); ?></p>
                            <p><strong>Status:</strong> 
                                <?php if ($row['status'] === 'accepted'): ?>
                                    <span class="status-accepted">Accepted</span>
                                <?php elseif ($row['status'] === 'rejected'): ?>
                                    <span class="status-rejected">Rejected</span>
                                <?php else: ?>
                                    Pending
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php if ($donation['status'] !== 'donated' && $row['status'] === 'pending'): ?>
                            <div class="action-buttons">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="action" value="accept" class="action-button accept-button">Accept</button>
                                    <button type="submit" name="action" value="reject" class="action-button reject-button">Reject</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No requests for this book yet.</p>
        <?php endif; ?>
        <a href="my_donations.php" class="back-link">Back to My Donations</a>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> BookSwap. All rights reserved.</p>
    </footer>
</body>
</html>

<?php
$stmt->close();
$donation_stmt->close();
$conn->close();
?>