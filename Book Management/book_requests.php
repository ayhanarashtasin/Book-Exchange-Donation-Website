<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'bookswap');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    die("Please log in to view book requests.");
}

$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;
$user_id = $_SESSION['user_id'];

// Confirm ownership
$check = $conn->prepare("SELECT * FROM books WHERE id = ? AND user_id = ?");
$check->bind_param("ii", $book_id, $user_id);
$check->execute();
$book = $check->get_result()->fetch_assoc();

if (!$book) {
    die("Book not found or access denied.");
}

// Fetch the book details of the requester's book (the book they are offering)
$book_details_sql = "
    SELECT b.title, b.author, b.genre
    FROM books b
    JOIN book_requests r ON r.requester_id = b.user_id
    WHERE r.book_id = ? AND r.requester_id != ?
    LIMIT 1";
$book_details_stmt = $conn->prepare($book_details_sql);
$book_details_stmt->bind_param("ii", $book_id, $user_id);
$book_details_stmt->execute();
$book_details_result = $book_details_stmt->get_result();
$book_details = $book_details_result->fetch_assoc();

// Handle Accept/Reject Action
if (isset($_POST['action']) && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $new_status = ($_POST['action'] == 'accept') ? 'accepted' : 'rejected';
    
    // Update request status
    $update_status_sql = "UPDATE book_requests SET status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_status_sql);
    $update_stmt->bind_param("si", $new_status, $request_id);
    $update_stmt->execute();
    
    // Refresh the page after updating status
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

$sql = "
    SELECT 
        r.id, r.request_date, r.status,
        u.name AS requester_name,
        u.city, u.zilla, u.email
    FROM book_requests r
    JOIN users u ON r.requester_id = u.id
    WHERE r.book_id = ?
    ORDER BY r.request_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Requests - BookSwap</title>
    <style>
        /* General Body */
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f8fafc;
            padding: 2rem;
            margin: 0;
        }

        h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: #2a3d66;
            font-size: 28px;
            animation: fadeIn 1s ease-in-out;
        }

        h3 {
            font-size: 24px;
            color: #4b5d6d;
            margin-bottom: 1rem;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            animation: slideUp 1s ease-out;
        }

        .book-details {
            background-color: #e9f4fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .book-details p {
            font-size: 18px;
            color: #4b5d6d;
        }

        /* Table Design */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #f0f4f8;
            transition: all 0.3s ease;
        }

        th {
            background-color: #2a3d66;
            color: white;
        }

        tr:hover {
            background-color: #f0f8ff;
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.3s ease, background-color 0.3s;
        }

        .btn-accept {
            background-color: #34d399;
            color: white;
        }

        .btn-reject {
            background-color: #ef4444;
            color: white;
        }

        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-accept:hover {
            background-color: #2bb67d;
        }

        .btn-reject:hover {
            background-color: #e11d48;
        }

        /* Footer Styling */
        footer {
            text-align: center;
            padding: 20px;
            background-color: #2a3d66;
            color: white;
            margin-top: 30px;
            font-size: 14px;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

    </style>
</head>
<body>

<div class="container">
    <h2>Requests for "<?php echo htmlspecialchars($book['title']); ?>"</h2>
    
    <?php if ($result->num_rows > 0 && $book_details): ?>
    <!-- Book Details Section - Only show when there are requests and book details exist -->
    <div class="book-details">
        <h3>Book Details</h3>
        <p><strong>Author:</strong> <?php echo htmlspecialchars($book_details['author']); ?></p>
        <p><strong>Genre:</strong> <?php echo htmlspecialchars($book_details['genre']); ?></p>
    </div>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <!-- Table to display requests -->
        <table>
            <thead>
                <tr>
                    <th>Requester</th>
                    <th>Location</th>
                    <th>Email</th>
                    <th>Requester Book</th>
                    <th>Status</th>
                    <th>Requested On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['requester_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['zilla'] . ', ' . $row['city']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <?php if ($book_details): ?>
                            <strong>Title:</strong> <?php echo htmlspecialchars($book_details['title']); ?><br>
                            <strong>Author:</strong> <?php echo htmlspecialchars($book_details['author']); ?><br>
                            <strong>Genre:</strong> <?php echo htmlspecialchars($book_details['genre']); ?>
                        <?php else: ?>
                            <em>No book details available</em>
                        <?php endif; ?>
                    </td>
                    <td><?php echo ucfirst($row['status']); ?></td>
                    <td><?php echo date("d M Y, H:i", strtotime($row['request_date'])); ?></td>
                    <td>
                        <?php if ($row['status'] == 'pending'): ?>
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <div class="action-buttons">
                                    <button type="submit" name="action" value="accept" class="btn btn-accept">Accept</button>
                                    <button type="submit" name="action" value="reject" class="btn btn-reject">Reject</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <span>No actions available</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align:center;">No requests for this book yet.</p>
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